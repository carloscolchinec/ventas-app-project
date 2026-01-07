<?php

namespace App\Http\Controllers;

use App\Mail\ContratoSocnet;
use App\Mail\EnvioContratoCliente;
use App\Models\MetodoPagoVenta;
use App\Models\Plan;
use App\Models\ServicioAdicionalVenta;
use App\Models\TipoBancoVenta;
use App\Models\VentasCliente;
use App\Models\VentasClienteCedula;
use App\Models\VentaPagoTarjeta;
use App\Models\VentaPagoCuenta;
use App\Models\VentaBeneficiario;
use App\Models\VentasHistorial;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;

class VentasClienteController extends Controller
{

    public function index(Request $request)
    {
        try {
            $user = auth()->user(); // usuario autenticado

            if ($user->rol === 'admin') {
                // Admin ve todas las ventas recientes
                $ventas = VentasClienteCedula::with(['beneficiario', 'metodoPago', 'pagoTarjeta', 'pagoCuenta.tipoBanco'])
                    ->latest()
                    ->take(100)
                    ->get();
            } else {
                // Solo ventas de este usuario
                $ventas = VentasClienteCedula::with(['beneficiario', 'metodoPago', 'pagoTarjeta', 'pagoCuenta.tipoBanco'])
                    ->where('id_usuario_registro', $user->id_usuario)
                    ->latest()
                    ->take(100)
                    ->get();
            }
            return response()->json([
                'ventas' => $ventas
            ]);
        } catch (Exception $e) {
            Log::error('Error al listar ventas', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);

            return response()->json([
                'mensaje' => 'Ocurrió un error al obtener las ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $venta = VentasClienteCedula::with(['beneficiario', 'serviciosAdicionales', 'metodoPago', 'pagoTarjeta', 'pagoCuenta.tipoBanco', 'planEntity'])
            ->where('id', $id)
            ->orWhere('identificacion', $id)
            ->firstOrFail();

        return response()->json($venta);
    }

    public function buscarPorFiltroPlan(Request $request)
    {
        Log::info('--- INICIO Filtro planes ---');
        Log::info('Body:', $request->all());
        Log::info('Query:', $request->query());

        try {
            // -------- 1) Entradas --------
            $rawMetodo = trim((string) $request->query('metodo_pago', ''));
            $discap = filter_var($request->query('discapacidad', false), FILTER_VALIDATE_BOOLEAN);
            $terceraEdad = filter_var($request->query('tercera_edad', false), FILTER_VALIDATE_BOOLEAN);
            $jubilado = filter_var($request->query('jubilado', false), FILTER_VALIDATE_BOOLEAN);

            Log::info("Params parsed: Metodo: $rawMetodo, Discap: $discap, 3rd: $terceraEdad, Jub: $jubilado");

            $promoFlash = filter_var($request->query('promo_flash', false), FILTER_VALIDATE_BOOLEAN);
            $beneficioDoble = filter_var($request->query('beneficio_doble', false), FILTER_VALIDATE_BOOLEAN);

            // -------- 2) Método de pago válido --------
            $metodoCodigo = null;
            if ($rawMetodo !== '') {
                if (is_numeric($rawMetodo)) {
                    $mp = MetodoPagoVenta::query()->find((int) $rawMetodo);
                    $metodoCodigo = $mp?->codigo; // 'FP01'..'FP04'
                } else {
                    $upper = strtoupper($rawMetodo);
                    $metodoCodigo = in_array($upper, ['FP01', 'FP02', 'FP03', 'FP04'], true) ? $upper : null;
                }
            }

            Log::info("MetodoCodigo final: $metodoCodigo");

            if (!$metodoCodigo) {
                Log::warning('No se encontro metodoCodigo, retornando vacio');
                return response()->json([
                    'ok' => true,
                    'metodo_pago' => null,
                    'elegible_ciudadano' => false,
                    'planes' => [],
                ]);
            }

            // -------- 3) Reglas base --------
            $esElegibleCiudadano = ($discap || $terceraEdad || $jubilado);
            $diasGratis = in_array($metodoCodigo, ['FP03', 'FP04'], true) ? 7 : 0;

            Log::info("Elegible Ciudadano: " . ($esElegibleCiudadano ? 'SI' : 'NO') . ", Dias Gratis: $diasGratis");

            // Helper: aplica filtro por método en pivot
            $withMetodo = function (\Illuminate\Database\Eloquent\Builder $q) use ($metodoCodigo) {
                $q->whereHas('metodos', function ($m) use ($metodoCodigo) {
                    $m->where('metodos_pagos_ventas.codigo', $metodoCodigo)
                        ->where('metodos_pagos_ventas.activo', 1);
                });
            };

            // Helpers de nombre
            $filtroNoPromosNiCiudadano = function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('nombre_plan', 'NOT LIKE', '%PROMO BIENVENIDA%')
                    ->where('nombre_plan', 'NOT LIKE', '%PROMO FLASH%')
                    ->where('nombre_plan', 'NOT LIKE', '%BENEFICIO DOBLE%')
                    ->where('nombre_plan', 'NOT LIKE', '%CIUDADANO%');
            };

            $planes = collect();

            // -------- 4) Lógica principal (según nombres en DB) --------
            // Ignoramos 'ZAPPING' en el nombre porque no está presente en la DB del cliente, 
            // nos guiamos por 'CIUDADANO' y 'PROMO BIENVENIDA'.

            if (in_array($metodoCodigo, ['FP01', 'FP02'], true)) {
                // EFECTIVO / TRANSFERENCIA / DEPÓSITO
                if ($esElegibleCiudadano) {
                    // Muestra: PLAN XXX CIUDADANO (SIN PROMO)
                    $qb = Plan::query()->activos()
                        ->where('nombre_plan', 'LIKE', '%CIUDADANO%')
                        ->where('nombre_plan', 'NOT LIKE', '%PROMO BIENVENIDA%');
                    $planes = $planes->merge($qb->get());
                } else {
                    // Muestra: PLAN XXX (NORMALES, SIN CIUDADANO, SIN PROMOS)
                    $qb = Plan::query()->activos()
                        ->where('nombre_plan', 'NOT LIKE', '%CIUDADANO%')
                        ->where('nombre_plan', 'NOT LIKE', '%PROMO%')
                        ->where('nombre_plan', 'NOT LIKE', '%BENEFICIO DOBLE%');
                    $planes = $planes->merge($qb->get());
                }
            } else {
                // TARJETA / DÉBITO (FP03 / FP04) -> PROMO BIENVENIDA
                if ($esElegibleCiudadano) {
                    // Ciudadano + Promo: PLAN XXX CIUDADANO PROMO BIENVENIDA
                    $qb = Plan::query()->activos()
                        ->where('nombre_plan', 'LIKE', '%CIUDADANO%')
                        ->where('nombre_plan', 'LIKE', '%PROMO BIENVENIDA%');
                    $planes = $planes->merge($qb->get());
                } else {
                    // Normal + Promo: PLAN XXX PROMO BIENVENIDA (SIN CIUDADANO)
                    $qb = Plan::query()->activos()
                        ->where('nombre_plan', 'NOT LIKE', '%CIUDADANO%')
                        ->where('nombre_plan', 'LIKE', '%PROMO BIENVENIDA%');
                    $planes = $planes->merge($qb->get());
                }
            }

            Log::info('Planes encontrados:', ['count' => $planes->count(), 'names' => $planes->pluck('nombre_plan')]);

            // -------- 5) Extras (respetando pivot si es posible) --------
            if ($promoFlash) {
                $qb = Plan::query()->where('estado', 'A')
                    ->where('nombre_plan', 'LIKE', '%PROMO FLASH%');
                // $withMetodo($qb);
                $planes = $planes->merge($qb->get());
            }

            if ($beneficioDoble) {
                $qb = Plan::query()->where('estado', 'A')
                    ->where('nombre_plan', 'LIKE', '%BENEFICIO DOBLE%');
                // $withMetodo($qb);
                $planes = $planes->merge($qb->get());
            }

            // -------- 6) Únicos y ordenados --------
            $planes = $planes->unique('id_plan')->sortBy('precio')->values();

            // -------- 7) Salida --------
            $data = $planes->map(function (Plan $p) use ($diasGratis, $metodoCodigo) {
                return [
                    'id_plan' => $p->id_plan,
                    'nombre_plan' => $p->nombre_plan,
                    'descripcion_plan' => $p->descripcion_plan,
                    'mb_subida' => $p->mb_subida,
                    'mb_bajada' => $p->mb_bajada,
                    'precio' => (float) $p->precio,
                    'nivel_comparticion' => $p->nivel_comparticion,
                    'tipo_red' => $p->tipo_red,
                    'estado' => $p->estado,
                    'dias_gratis' => $diasGratis,
                    'metodo_pago' => $metodoCodigo,
                ];
            });

            Log::alert($data);

            return response()->json([
                'ok' => true,
                'metodo_pago' => $metodoCodigo,
                'elegible_ciudadano' => $esElegibleCiudadano,
                'planes' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al filtrar planes', ['msg' => $e->getMessage()]);
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al obtener los planes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function feedNotificaciones(Request $request)
    {
        // Parámetros simples para feed de la campana
        $limit = min((int) $request->get('limit', 10), 50);     // máx 50
        $hours = min((int) $request->get('hours', 24), 168);    // últimas 24h (máx 7 días)

        $user = auth()->user();
        $vendedorId = $user->id_usuario ?? $user->id;         // adapta a tu esquema
        $now = Carbon::now();
        $from = $now->copy()->subHours($hours);

        $ventas = VentasClienteCedula::query()
            ->where('id_usuario_registro', $vendedorId)
            ->whereBetween('created_at', [$from, $now])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get([
                'id',
                'nombres',
                'apellidos',
                'identificacion',
                'plan',
                'estado',
                'serie_contrato',
                'created_at',
            ]);

        $mapEstado = [
            'VI' => 'Venta Ingresada',
            'VR' => 'Venta Rechazada',
            'IEP' => 'Instalación en Proceso',
        ];

        $items = $ventas->map(function ($v) use ($mapEstado) {
            return [
                'id' => $v->id,
                'title' => 'Venta registrada',
                // Texto compacto ideal para el modal/alert de la campana
                'subtitle' => "{$v->nombres} {$v->apellidos} • {$v->plan}" . ($v->serie_contrato ? " • {$v->serie_contrato}" : ''),
                'estado' => $mapEstado[$v->estado] ?? $v->estado,
                'timestamp' => optional($v->created_at)->toIso8601String(),
                'icon' => 'document-text-outline',
            ];
        })->values();

        return response()->json([
            'count' => $ventas->count(),                   // para el badge
            'items' => $items,                             // lista simple
            'from' => $from->toIso8601String(),
            'to' => $now->toIso8601String(),
        ]);
    }

    public function estadisticas(Request $request)
    {
        try {
            $user = auth()->user();
            $mesActual = Carbon::now()->month;
            $anioActual = Carbon::now()->year;

            $query = VentasClienteCedula::whereYear('created_at', $anioActual)
                ->whereMonth('created_at', $mesActual);

            if ($user->rol !== 'admin') {
                $query->where('id_usuario_registro', $user->id_usuario);
            }

            $stats = $query->selectRaw('estado, count(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado');

            return response()->json([
                'registrados' => $stats['VI'] ?? 0,
                'rechazados' => $stats['VR'] ?? 0,
                'proceso_instalacion' => $stats['IEP'] ?? 0
            ]);
        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas de ventas', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);

            return response()->json([
                'mensaje' => 'Ocurrió un error al obtener las estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            Log::info('📥 DATOS PRINCIPALES RECIBIDOS');

            // ==== Parseo JSON desde el móvil (strings JSON) ====
            $telefonos = json_decode($request->telefonos, true) ?? [];
            $correos = json_decode($request->correos, true) ?? [];
            $serviciosAdicionales = json_decode($request->servicios_adicionales, true) ?? [];

            // ==== Validación base ====
            $request->validate([
                'tipo_documento' => 'required|string|max:20',
                'identificacion' => 'required|string|max:20|unique:ventas_clientes_cedula',
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'fecha_nacimiento' => 'required|date',
                'direccion' => 'required|string|max:255',
                'ciudad' => 'required|string|max:100',
                'provincia' => 'required|string|max:100',
                'telefonos' => 'required',
                'correos' => 'required',
                'plan' => 'required|string|max:255',
                'tipo_plan_contratado' => 'nullable|string',

                // === Pago (siempre pedir método) ===
                'metodo_pago_id' => 'required|exists:metodos_pagos_ventas,id',
                'dia_pago' => 'required|in:10,20', // NUEVO: Día de Pago obligatorio
                // lo demás se valida condicionalmente abajo
            ]);

            // === Reglas condicionales según método ===
            $metodo = MetodoPagoVenta::find($request->metodo_pago_id);
            $codigoMetodo = $metodo?->codigo;  // EFECTIVO | TRANSFERENCIA | TARJETA_CREDITO | DEBITO_BANCARIO

            Log::info('📥 DATOS COMPLETOS RECIBIDOS', $request->all());

            Log::info('💰 Venta Store - Debug Pago', [
                'metodo_id_enviado' => $request->metodo_pago_id,
                'metodo_obj_encontrado' => $metodo,
                'codigo_resuelto' => $codigoMetodo,
                'request_keys' => array_keys($request->all())
            ]);

            $textoMetodo = strtoupper($metodo?->nombre ?? '');

            // Deteccion flexible (FP04, DEBITO, etc)
            $esTarjeta = ($codigoMetodo === 'TARJETA_CREDITO' || str_contains($textoMetodo, 'TARJETA') || str_contains($textoMetodo, 'CREDITO'));
            $esDebito = ($codigoMetodo === 'DEBITO_BANCARIO' || str_contains($textoMetodo, 'DEBITO') || str_contains($textoMetodo, 'BANCARIO') || $codigoMetodo === 'FP04');

            Log::info('💰 Venta Store - Clasificacion Pago', [
                'es_tarjeta' => $esTarjeta,
                'es_debito' => $esDebito
            ]);

            if ($esTarjeta) {
                Log::info("💳 Validando Tarjeta. Exp recibido: " . $request->tarjeta_exp);
                $request->validate([
                    'tarjeta_numero_enc' => 'required|string',
                    'tarjeta_exp' => ['required', 'regex:/^\d{2}\/\d{2}$/'],
                    'tarjeta_last4' => 'required|digits:4',
                ]);
            }

            if ($esDebito) {
                // Validación flexible para el tipo de cuenta bancaria
                $request->validate([
                    'tipo_banco_id' => 'required|exists:tipo_banco_ventas,id',
                    'cuenta_numero_enc' => 'required|string',
                    // Aceptamos cualquiera de los nombres de llave por compatibilidad
                    'tipo_cuenta_banco' => 'nullable|string',
                    'tipo_cuenta_bancaria' => 'nullable|string',
                    'tipo_cuenta' => 'required|string',
                ]);
            }
            // TRANSFERENCIA: solo método, sin más datos
            // EFECTIVO: solo método

            // === Serie de contrato ===
            $ident = $request->identificacion;
            $fecha = Carbon::parse($request->fecha_nacimiento)->format('dmy');
            $codigo_contrato = 'COT-' . $ident . '-' . $fecha;

            // === Archivos ===
            $rutaFirma = $request->hasFile('firma')
                ? $request->file('firma')->storeAs("contratos/{$ident}", 'firma.jpg', 'public')
                : null;

            $cedulaFrontal = $request->hasFile('cedula_frontal')
                ? $request->file('cedula_frontal')->storeAs("contratos/{$ident}", 'cedula_frontal.jpg', 'public')
                : null;

            $cedulaTrasera = $request->hasFile('cedula_trasera')
                ? $request->file('cedula_trasera')->storeAs("contratos/{$ident}", 'cedula_trasera.jpg', 'public')
                : null;

            $planillaLuz = $request->hasFile('planilla_luz')
                ? $request->file('planilla_luz')->storeAs("contratos/{$ident}", 'planilla_luz.jpg', 'public')
                : null;

            // --- Archivos Beneficiario (Si aplica) ---
            $benCedulaFrontal = $request->hasFile('beneficiario_cedula_frontal')
                ? $request->file('beneficiario_cedula_frontal')->storeAs("contratos/{$ident}", 'beneficiario_cedula_frontal.jpg', 'public')
                : null;

            $benCedulaTrasera = $request->hasFile('beneficiario_cedula_trasera')
                ? $request->file('beneficiario_cedula_trasera')->storeAs("contratos/{$ident}", 'beneficiario_cedula_trasera.jpg', 'public')
                : null;

            $benCarnet = $request->hasFile('beneficiario_carnet')
                ? $request->file('beneficiario_carnet')->storeAs("contratos/{$ident}", 'beneficiario_carnet.jpg', 'public')
                : null;

            // === Usuario autenticado ===
            $usuarioId = auth()->user()->id_usuario;

            // === Cifrado de números sensibles (si no usas cast 'encrypted') ===
            $tarjetaNumeroEnc = $request->filled('tarjeta_numero_enc')
                ? Crypt::encryptString($request->tarjeta_numero_enc) : null;

            $cuentaNumeroEnc = $request->filled('cuenta_numero_enc')
                ? Crypt::encryptString($request->cuenta_numero_enc) : null;

            // === Determinar Tipo de Cuenta Bancaria (Flexible y Seguro) ===
            $bankAccountType = $request->tipo_cuenta_banco ?? $request->tipo_cuenta_bancaria;

            // Si es nulo, buscar en 'tipo_cuenta' pero solo si contiene valores bancarios reales
            if (!$bankAccountType && $request->has('tipo_cuenta')) {
                $val = strtoupper($request->tipo_cuenta);
                if (in_array($val, ['AHORRO', 'CORRIENTE', 'AHORROS'])) {
                    $bankAccountType = $val;
                }
            }

            // === Crear registro principal ===
            $cliente = VentasClienteCedula::create([
                'tipo_documento' => $request->tipo_documento,
                'serie_contrato' => $codigo_contrato,
                'identificacion' => $ident,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'es_tercera_edad' => filter_var($request->es_tercera_edad, FILTER_VALIDATE_BOOLEAN),
                'es_presenta_discapacidad' => filter_var($request->es_presenta_discapacidad, FILTER_VALIDATE_BOOLEAN),
                'direccion' => $request->direccion,
                'referencia_domiciliaria' => $request->referencia_domiciliaria,
                'ciudad' => $request->ciudad,
                'provincia' => $request->provincia,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'telefonos' => $telefonos,
                'correos' => $correos,
                'establecimiento' => $request->establecimiento,
                'tipo_cuenta_otro' => $request->tipo_cuenta_otro,
                'plan' => $request->plan,
                'red_acceso' => $request->red_acceso,
                'nivel_comparticion' => $request->nivel_comparticion,
                'dias_gratis' => $request->dias_gratis,

                // Si sigues usando JSON de servicios por ahora:
                'servicios_adicionales' => $serviciosAdicionales,

                // Archivos
                'cedula_frontal' => $cedulaFrontal,
                'cedula_trasera' => $cedulaTrasera,
                'planilla_luz' => $planillaLuz,
                'firma' => $rutaFirma,

                // Estado & usuario
                'estado' => 'VI',
                'id_usuario_registro' => $usuarioId,

                // ====== Pago ======
                'metodo_pago_id' => $request->metodo_pago_id,
                'metodo_pago_texto' => $metodo?->nombre,
                'dia_pago' => $request->dia_pago,
            ]);

            // === Guardar Detalle Pagos (Relaciones) ===
            if ($esTarjeta) {
                VentaPagoTarjeta::create([
                    'venta_id' => $cliente->id,
                    'tarjeta_numero_enc' => $tarjetaNumeroEnc,
                    'tarjeta_last4' => $request->tarjeta_last4,
                    'tarjeta_exp' => $request->tarjeta_exp,
                ]);
            }

            if ($esDebito) {
                VentaPagoCuenta::create([
                    'venta_id' => $cliente->id,
                    'tipo_banco_id' => $request->tipo_banco_id,
                    'cuenta_numero_enc' => $cuentaNumeroEnc,
                    'tipo_cuenta' => $bankAccountType,
                ]);
            }

            // === Guardar Beneficiario (Si se envió data) ===
            if ($request->filled('beneficiario_identificacion')) {
                VentaBeneficiario::create([
                    'venta_id' => $cliente->id,
                    'identificacion' => $request->beneficiario_identificacion,
                    'nombres' => $request->beneficiario_nombres,
                    'apellidos' => $request->beneficiario_apellidos,
                    'porcentaje' => $request->beneficiario_porcentaje ?? 0,
                    'cedula_frontal' => $benCedulaFrontal,
                    'cedula_trasera' => $benCedulaTrasera,
                    'carnet' => $benCarnet,
                ]);
            }

            // === Servicios Adicionales ===
            $serviciosIds = json_decode($request->servicios_adicionales, true) ?? [];
            if (!empty($serviciosIds)) {
                $syncData = [];
                foreach ($serviciosIds as $sid) {
                    $servicio = ServicioAdicionalVenta::find($sid);
                    if ($servicio) {
                        $syncData[$sid] = [
                            'precio_unitario' => $servicio->precio,
                            'cantidad' => 1,
                            'total' => $servicio->precio,
                        ];
                    }
                }
                if (!empty($syncData)) {
                    $cliente->serviciosAdicionales()->sync($syncData);
                }
            }

            // === Generar y guardar contrato PDF ===
            $plan = Plan::where('nombre_plan', $cliente->plan)->first();

            $datosPrestador = match ($cliente->establecimiento) {
                'Matriz - Guayaquil' => [
                    'ciudad_fecha' => 'GUAYAQUIL',
                    'direccion' => 'BARRIO GUAYAQUIL #430',
                    'provincia' => 'SANTA ELENA',
                    'ciudad' => 'SANTA ELENA',
                    'canton' => 'SANTA ELENA',
                    'parroquia' => 'SAN JOSÉ DE ANCÓN',
                    'telefono' => '0958933197'
                ],
                'Sucursal - San José de Ancón' => [
                    'ciudad_fecha' => 'SANTA ELENA',
                    'direccion' => 'BARRIO GUAYAQUIL #430',
                    'provincia' => 'SANTA ELENA',
                    'ciudad' => 'SANTA ELENA',
                    'canton' => 'SANTA ELENA',
                    'parroquia' => 'SAN JOSÉ DE ANCÓN',
                    'telefono' => '0958933197'
                ],
                'Sucursal - José Luis Tamayo (MUEY)' => [
                    'ciudad_fecha' => 'SALINAS',
                    'direccion' => 'DIRECCIÓN: CDLA. SANTA PAULA AVD.14 CALLE 18/17',
                    'provincia' => 'SANTA ELENA',
                    'ciudad' => 'SANTA ELENA',
                    'canton' => 'SALINAS',
                    'parroquia' => 'JOSE LUIS TAMAYO (MUEY)',
                    'telefono' => '0958933197 - 043903497'
                ],
                default => [
                    'ciudad_fecha' => 'GUAYAQUIL',
                    'direccion' => 'BARRIO GUAYAQUIL #430',
                    'provincia' => 'SANTA ELENA',
                    'ciudad' => 'SANTA ELENA',
                    'canton' => 'SANTA ELENA',
                    'parroquia' => 'SAN JOSÉ DE ANCÓN',
                    'telefono' => '0958933197'
                ],
            };

            // === Preparar datos para Anexo Débito ===
            $authDebito = [
                'mostrar' => false,
                'tipo' => '', // TARJETA_CREDITO | DEBITO_BANCARIO
                'banco' => '',
                'numero' => '',
                'expiracion' => '',
                'titular' => mb_strtoupper($cliente->nombres . ' ' . $cliente->apellidos),
                'identificacion' => $cliente->identificacion,
                'tipo_cuenta' => '', // AHORROS | CORRIENTE
            ];

            if ($esTarjeta) {
                $authDebito['mostrar'] = true;
                $authDebito['tipo'] = 'TARJETA_CREDITO';
                $authDebito['numero'] = $request->tarjeta_numero_enc; // En claro
                $authDebito['expiracion'] = $request->tarjeta_exp;
            } elseif ($esDebito) {
                $authDebito['mostrar'] = true;
                $authDebito['tipo'] = 'DEBITO_BANCARIO';
                $authDebito['numero'] = $request->cuenta_numero_enc; // En claro
                if ($request->tipo_banco_id && $banco = TipoBancoVenta::find($request->tipo_banco_id)) {
                    $authDebito['banco'] = $banco->nombre;
                }
                $authDebito['tipo_cuenta'] = $request->tipo_cuenta; // AHORROS o CORRIENTE
            }

            Log::info('📄 Generando PDF...');
            $cliente->load('serviciosAdicionales');
            $pdf = Pdf::loadView('contratos.contrato_master', compact('cliente', 'plan', 'datosPrestador', 'authDebito'))->setPaper('A4');
            Log::info('✅ PDF Generado. Guardando en Storage...');
            $nombreArchivoPdf = 'contrato_' . $cliente->identificacion . '.pdf';
            Storage::disk('public')->put("contratos/{$cliente->identificacion}/{$nombreArchivoPdf}", $pdf->output());

            // === Envío de correo a los correos válidos del cliente ===
            if (!empty($cliente->correos) && is_array($cliente->correos)) {
                foreach ($cliente->correos as $correo) {
                    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                        Log::info("📧 Enviando correo cliente: $correo");
                        Mail::to($correo)->send(new ContratoSocnet($cliente, $pdf->output()));
                        Log::info("✅ Correo cliente enviado.");
                    }
                }
            }

            // === Notificación a ventas (con reply-to del vendedor si aplica) ===
            $vendedor = auth()->user();
            $vendedorNombre = trim(($vendedor->nombres ?? $vendedor->nombre ?? '') . ' ' . ($vendedor->apellidos ?? $vendedor->apellido ?? ''))
                ?: ($vendedor->name ?? 'Vendedor');
            $vendedorEmail = $vendedor->correo_electronico ?? $vendedor->email ?? null;

            $mailable = new \App\Mail\NotificaVentaVendedor($cliente, $plan, $vendedorNombre, $pdf->output());
            if ($vendedorEmail && filter_var($vendedorEmail, FILTER_VALIDATE_EMAIL)) {
                $mailable->replyTo($vendedorEmail, $vendedorNombre);
            }
            Log::info("📧 Enviando notificación ventas...");
            Mail::to('ventas@seroficom.org')->send($mailable);
            Log::info("✅ Notificación ventas enviada.");

            return response()->json([
                'mensaje' => 'Cliente registrado con éxito',
                'cliente' => $cliente
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Error de validación',
                'error' => $e->validator->errors()->first()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('❌ Error al registrar cliente', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json([
                'mensaje' => 'Ocurrió un error al guardar los datos',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function mostrarContrato($id)
    {
        Log::info("📄 Solicitud mostrar contrato para ID/Ident: $id");

        // Puede ser el ID (int) o la Identificación (string)
        $cliente = VentasClienteCedula::where('id', $id)
            ->orWhere('identificacion', $id)
            ->firstOrFail();

        $ci = $cliente->identificacion;
        $path = "contratos/{$ci}/contrato_{$ci}.pdf";

        // Si el archivo físico existe en public storage
        if (Storage::disk('public')->exists($path)) {
            Log::info("✅ Enviando PDF existente desde Storage: $path");
            return response()->file(storage_path("app/public/{$path}"), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="contrato.pdf"',
            ]);
        }

        Log::warning("⚠️ PDF no existe en storage, regenerando...");

        // Si no existe, lo generamos dinámicamente
        $plan = Plan::where('nombre_plan', $cliente->plan)->first();

        // Selección de vista según lógica (por ahora por defecto 1)
        // NOTA: Aquí deberíamos usar la misma lógica de establecimiento si tuviéramos el establecimiento en $seleccion.
        // Asumiendo que $cliente->establecimiento tiene el dato correcto:

        $datosPrestador = match ($cliente->establecimiento) {
            'Matriz - Guayaquil' => [
                'ciudad_fecha' => 'GUAYAQUIL',
                'direccion' => 'BARRIO GUAYAQUIL #430',
                'provincia' => 'SANTA ELENA',
                'ciudad' => 'SANTA ELENA',
                'canton' => 'SANTA ELENA',
                'parroquia' => 'SAN JOSÉ DE ANCÓN',
                'telefono' => '0958933197'
            ],
            'Sucursal - San José de Ancón' => [
                'ciudad_fecha' => 'SANTA ELENA',
                'direccion' => 'BARRIO GUAYAQUIL #430',
                'provincia' => 'SANTA ELENA',
                'ciudad' => 'SANTA ELENA',
                'canton' => 'SANTA ELENA',
                'parroquia' => 'SAN JOSÉ DE ANCÓN',
                'telefono' => '0958933197'
            ],
            'Sucursal - José Luis Tamayo (MUEY)' => [
                'ciudad_fecha' => 'SALINAS',
                'direccion' => 'DIRECCIÓN: CDLA. SANTA PAULA AVD.14 CALLE 18/17',
                'provincia' => 'SANTA ELENA',
                'ciudad' => 'SANTA ELENA',
                'canton' => 'SALINAS',
                'parroquia' => 'JOSE LUIS TAMAYO (MUEY)',
                'telefono' => '0958933197 - 043903497'
            ],
            default => [
                'ciudad_fecha' => 'GUAYAQUIL',
                'direccion' => 'BARRIO GUAYAQUIL #430',
                'provincia' => 'SANTA ELENA',
                'ciudad' => 'SANTA ELENA',
                'canton' => 'SANTA ELENA',
                'parroquia' => 'SAN JOSÉ DE ANCÓN',
                'telefono' => '0958933197'
            ],
        };

        // === Preparar datos para Anexo Débito (Decrypt) ===
        $cliente->load(['metodoPago', 'pagoTarjeta', 'pagoCuenta.tipoBanco']);
        $codigoMetodo = $cliente->metodoPago?->codigo;
        $textoMetodo = strtoupper($cliente->metodoPago?->nombre ?? '');

        $esTarjeta = ($codigoMetodo === 'TARJETA_CREDITO' || str_contains($textoMetodo, 'TARJETA') || str_contains($textoMetodo, 'CREDITO') || $codigoMetodo === 'FP03');
        $esDebito = ($codigoMetodo === 'DEBITO_BANCARIO' || str_contains($textoMetodo, 'DEBITO') || str_contains($textoMetodo, 'BANCARIO') || $codigoMetodo === 'FP04');

        $authDebito = [
            'mostrar' => false,
            'tipo' => '',
            'banco' => '',
            'numero' => '',
            'expiracion' => '',
            'titular' => mb_strtoupper($cliente->nombres . ' ' . $cliente->apellidos),
            'identificacion' => $cliente->identificacion,
        ];

        if ($esTarjeta) {
            $authDebito['mostrar'] = true;
            $authDebito['tipo'] = 'TARJETA_CREDITO';
            try {
                $enc = $cliente->pagoTarjeta?->tarjeta_numero_enc;
                $authDebito['numero'] = $enc ? Crypt::decryptString($enc) : '';
            } catch (\Exception $e) {
                $authDebito['numero'] = 'Error al descifrar';
            }
            $authDebito['expiracion'] = $cliente->pagoTarjeta?->tarjeta_exp;
        } elseif ($esDebito) {
            $authDebito['mostrar'] = true;
            $authDebito['tipo'] = 'DEBITO_BANCARIO';
            try {
                $enc = $cliente->pagoCuenta?->cuenta_numero_enc;
                $authDebito['numero'] = $enc ? Crypt::decryptString($enc) : '';
            } catch (\Exception $e) {
                $authDebito['numero'] = 'Error al descifrar';
            }
            // Banco desde la relación
            if ($cliente->pagoCuenta && $cliente->pagoCuenta->tipoBanco) {
                $authDebito['banco'] = $cliente->pagoCuenta->tipoBanco->nombre;
            }
            if ($cliente->pagoCuenta) {
                // FIX: Usar optional por si acaso
                $authDebito['tipo_cuenta'] = $cliente->pagoCuenta->tipo_cuenta;
            }
        }
        $pdf = Pdf::loadView('contratos.contrato_master', compact('cliente', 'plan', 'datosPrestador', 'authDebito'))->setPaper('A4');
        return $pdf->stream("Contrato-{$ci}.pdf");
    }


    private function guardarArchivo($file, $path)
    {
        Storage::disk('public')->put($path, file_get_contents($file));
        return $path;
    }

    public function uploadImagenes(Request $request, $id)
    {
        try {
            $cliente = VentasCliente::findOrFail($id);

            // Verifica si la imagen fue enviada con el nombre correcto
            if (!$request->hasFile('imagen')) {
                return response()->json(['error' => 'No se envió ninguna imagen'], 400);
            }

            $imagen = $request->file('imagen');

            $ruta = "contratos/{$id}/" . uniqid() . '.' . $imagen->getClientOriginalExtension();
            Storage::disk('public')->put($ruta, file_get_contents($imagen));

            // Decodifica si ya existen imágenes anteriores
            $imagenesAnteriores = is_array($cliente->imagenes) ? $cliente->imagenes : json_decode($cliente->imagenes ?? '[]', true);
            $imagenesActualizadas = array_merge($imagenesAnteriores, [$ruta]);

            $cliente->imagenes = json_encode($imagenesActualizadas);
            $cliente->save();

            return response()->json([
                'mensaje' => 'Imagen subida con éxito',
                'imagenes' => $imagenesActualizadas
            ]);
        } catch (\Exception $e) {
            Log::error('Error subiendo imagen', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);
            return response()->json(['error' => 'Error al subir imagen'], 500);
        }
    }


    public function comisiones(Request $request)
    {
        try {
            $user = auth()->user();
            $userId = $user->id_usuario ?? $user->id ?? auth()->id();
            $isAdmin = $this->esAdmin($user);

            // --- Selección del período etiquetado (28 -> 27) ---
            if ($request->filled('period_year') && $request->filled('period_month')) {
                $year = (int) $request->query('period_year');
                $mon = (int) $request->query('period_month');
                $period = $this->periodoPorEtiqueta($year, $mon);
            } else {
                $when = $request->query('when', 'previous'); // por defecto, mes vencido
                if ($when === 'current') {
                    $period = $this->periodoEtiquetadoQueContiene(Carbon::now());
                } else { // 'previous'
                    $cur = $this->periodoEtiquetadoQueContiene(Carbon::now());
                    $period = $this->periodoPorEtiqueta(
                        $this->mesAnteriorEtiquetaYear($cur['year'], $cur['month']),
                        $this->mesAnteriorEtiquetaMonth($cur['year'], $cur['month'])
                    );
                }
            }

            // --- Ventas del usuario en el período ---
            $q = VentasClienteCedula::query()
                ->where('estado', 'VI')
                ->whereBetween('created_at', [$period['start'], $period['end']]);

            if ($isAdmin && $request->filled('user_id')) {
                $q->where('id_usuario_registro', (int) $request->query('user_id'));
            } else {
                $q->where('id_usuario_registro', $userId);
            }

            $ventas = $q->get(['id', 'serie_contrato', 'plan', 'created_at']);

            // --- Calcular comisiones ---
            $items = [];
            $total = 0.0;

            foreach ($ventas as $v) {
                $unit = $this->comisionPorPlan($v->plan);
                $items[] = [
                    'id' => $v->id,
                    'serie_contrato' => $v->serie_contrato,
                    'plan' => $v->plan,
                    'comision_unit' => $unit,
                    'created_at' => optional($v->created_at)->toIso8601String(),
                ];
                $total += $unit;
            }

            // Desglose por plan
            $breakdown = $this->agruparPorPlan(collect($items));

            return response()->json([
                'period' => [
                    'label' => $period['label'],           // "Septiembre 2025"
                    'start' => $period['start']->toIso8601String(), // 28/08 00:00
                    'end' => $period['end']->toIso8601String(),   // 27/09 23:59:59
                    'month' => $period['month'],           // 9
                    'year' => $period['year'],            // 2025
                    'fecha_corte' => $period['end']->format('d/m/Y'),
                ],
                'resumen' => [
                    'ventas' => count($items),
                    'comision_total' => round($total, 2),
                ],
                'breakdown' => $breakdown,  // [{plan,comision_unit,cantidad,comision_total}]
                'ventas' => $items,      // lista con serie y comisión por venta
            ]);
        } catch (\Throwable $e) {
            Log::error('Comisiones mes etiquetado', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Error al calcular comisiones'], 500);
        }
    }


    private function esAdmin($user): bool
    {
        return in_array(Str::lower($user->rol ?? ''), ['admin', 'administrador'], true);
    }

    // Devuelve el período etiquetado (yyyy-mm) que CONTIENE la fecha $d.
    // Si d=2025-09-24 → etiqueta = 2025-09, start=2025-08-28 00:00:00, end=2025-09-27 23:59:59
    private function periodoEtiquetadoQueContiene(Carbon $d): array
    {
        $d = $d->copy();
        $labelYear = $d->day <= 27 ? $d->year : $d->copy()->addMonth()->year;
        $labelMon = $d->day <= 27 ? $d->month : $d->copy()->addMonth()->month;

        return $this->periodoPorEtiqueta($labelYear, $labelMon);
    }

    // A partir de (year, month) de la ETIQUETA (el mes del día 27)
    private function periodoPorEtiqueta(int $year, int $month): array
    {
        // Fin del período = 27 del mes etiquetado, 23:59:59
        $end = Carbon::create($year, $month, 27, 23, 59, 59);
        // Inicio = 28 del mes anterior, 00:00:00
        $start = $end->copy()->subMonthNoOverflow()->day(28)->startOfDay();

        return [
            'start' => $start,
            'end' => $end,
            'year' => $year,
            'month' => $month,
            'label' => $this->mesEs($month) . ' ' . $year, // "Septiembre 2025"
        ];
    }

    // Para “previous” a partir de (year,month) etiquetado actual
    private function mesAnteriorEtiquetaYear(int $year, int $month): int
    {
        $d = Carbon::create($year, $month, 1)->subMonth();
        return (int) $d->year;
    }
    private function mesAnteriorEtiquetaMonth(int $year, int $month): int
    {
        $d = Carbon::create($year, $month, 1)->subMonth();
        return (int) $d->month;
    }

    private function mesEs(int $m): string
    {
        $n = [
            1 => 'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        ];
        return $n[$m] ?? (string) $m;
    }


    // Normaliza el nombre para agrupar y devuelve comisión unit
    private function comisionPorPlan(?string $plan): float
    {
        if (!$plan)
            return 0.0;

        $vel = $this->extraerVelocidad($plan);     // 60 / 100 / 140 / null
        $sin = $this->esSinZapping($plan);         // true si texto dice “sin zapping”

        // Mapa de comisiones
        $map = [
            // con Zapping
            '60_0' => 3.0,
            '100_0' => 4.0,
            '140_0' => 5.0,
            // sin Zapping
            '60_1' => 2.0,
            '100_1' => 3.0,
            '140_1' => 4.0,
        ];

        $key = ($vel ?: '0') . '_' . ($sin ? '1' : '0');
        return (float) ($map[$key] ?? 0.0);
    }

    // 60 / 100 / 140 a partir del string del plan
    private function extraerVelocidad(string $plan): ?int
    {
        if (preg_match('/(\d+)\s*mb/i', $plan, $m)) {
            $n = (int) $m[1];
            if (in_array($n, [60, 100, 140], true))
                return $n;
        }
        if (preg_match('/(\d+)\s*mp?s/i', $plan, $m)) {
            $n = (int) $m[1];
            if (in_array($n, [60, 100, 140], true))
                return $n;
        }
        return null;
    }

    private function esSinZapping(string $plan): bool
    {
        $s = Str::lower(Str::ascii($plan));
        // Cubre “Sin Zapping”, “(Sin ZappingTV)”, etc.
        return Str::contains($s, ['sin zapping', 'sin zappingtv', '(sin zapping', 'sin zapping)']);
    }

    // Para etiquetar filas del “desglose por plan”
    private function nombreCanonicoPlan(?string $plan): string
    {
        $vel = $this->extraerVelocidad($plan ?? '') ?? 0;
        $sin = $this->esSinZapping($plan ?? '');
        if (!$vel)
            return trim($plan ?? 'Plan desconocido');
        return $sin ? "{$vel}Mbps (Sin ZappingTV)" : "{$vel}Mbps + ZappingTV";
    }

    // Desglose por plan
    private function agruparPorPlan(Collection $items): array
    {
        $grupos = [];
        foreach ($items as $it) {
            $canon = $this->nombreCanonicoPlan($it['plan'] ?? null);
            $unit = (float) ($it['comision_unit'] ?? 0);

            if (!isset($grupos[$canon])) {
                $grupos[$canon] = [
                    'plan' => $canon,
                    'comision_unit' => $unit,
                    'cantidad' => 0,
                    'comision_total' => 0.0,
                ];
            }
            $grupos[$canon]['cantidad'] += 1;
            $grupos[$canon]['comision_total'] = round($grupos[$canon]['comision_total'] + $unit, 2);
        }

        // Orden por velocidad y luego por “sin/with zapping”
        $rows = array_values($grupos);
        usort($rows, function ($a, $b) {
            $av = $this->extraerVelocidad($a['plan']) ?? 0;
            $bv = $this->extraerVelocidad($b['plan']) ?? 0;
            if ($av !== $bv)
                return $av <=> $bv;
            return strcmp($a['plan'], $b['plan']);
        });

        return $rows;
    }

    public function verificarIdentificacion(Request $request)
    {
        try {
            // 1) Validar input
            $request->validate([
                'identificacion' => 'required|string|max:20',
            ]);

            $identificacion = $request->query('identificacion');

            // 2) Buscar ventas por esa identificación
            //    Si quieres considerar TODO (incluyendo rechazadas), quita el whereIn.
            $venta = VentasClienteCedula::query()
                ->where('identificacion', $identificacion)
                ->whereIn('estado', ['VI', 'IEP']) // <- solo ventas activas / en proceso
                ->latest()
                ->first();

            // 3) Armar respuesta
            if (!$venta) {
                return response()->json([
                    'ok' => true,
                    'exists' => false,
                    'identificacion' => $identificacion,
                    'message' => 'La identificación no registra ventas activas.',
                ]);
            }

            return response()->json([
                'ok' => true,
                'exists' => true,
                'identificacion' => $identificacion,
                'message' => 'La identificación ya registra una venta.',
                'venta' => [
                    'id' => $venta->id,
                    'nombres' => $venta->nombres,
                    'apellidos' => $venta->apellidos,
                    'fecha_nacimiento' => $venta->fecha_nacimiento,
                    'es_tercera_edad' => $venta->es_tercera_edad,
                    'es_presenta_discapacidad' => $venta->es_presenta_discapacidad,
                    'direccion' => $venta->direccion,
                    'referencia_domiciliaria' => $venta->referencia_domiciliaria,
                    'ciudad' => $venta->ciudad,
                    'provincia' => $venta->provincia,
                    'latitud' => $venta->latitud,
                    'longitud' => $venta->longitud,
                    'telefonos' => $venta->telefonos, // Array/JSON casted automatically by model? Check model cast
                    'correos' => $venta->correos,
                    'estado' => $venta->estado,
                    'plan' => $venta->plan,
                    'serie_contrato' => $venta->serie_contrato,
                    'created_at' => optional($venta->created_at)->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al verificar identificación de venta', [
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Error al verificar la identificación.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
