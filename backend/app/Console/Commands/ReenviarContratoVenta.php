<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReenviarContratoVenta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'venta:reenviar-contrato {id} {--email= : Correo destino opcional}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reenvía el correo del contrato con PDF generado al vuelo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        $emailDestino = $this->option('email');

        $this->info("Buscando venta ID: $id");

        $cliente = \App\Models\VentasClienteCedula::with(['metodoPago'])->find($id);

        if (!$cliente) {
            $this->error("No se encontró la venta.");
            return \Illuminate\Console\Command::FAILURE;
        }

        $plan = \App\Models\Plan::where('nombre_plan', $cliente->plan)->first();

        // --- Logica de Datos Prestador (Copiada del Controller) ---
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
            ]
        };

        // --- Logica de Auth Debito (Copiada / Decrypt) ---
        $cliente->load(['metodoPago', 'pagoTarjeta', 'pagoCuenta.tipoBanco']);
        $codigoMetodo = $cliente->metodoPago?->codigo;
        $authDebito = [
            'mostrar' => false,
            'tipo' => '',
            'banco' => '',
            'numero' => '',
            'expiracion' => '',
            'titular' => mb_strtoupper($cliente->nombres . ' ' . $cliente->apellidos),
            'identificacion' => $cliente->identificacion,
        ];

        $textoMetodo = strtoupper($cliente->metodoPago?->nombre ?? '');

        $esTarjeta = ($codigoMetodo === 'TARJETA_CREDITO' || str_contains($textoMetodo, 'TARJETA') || str_contains($textoMetodo, 'CREDITO') || $codigoMetodo === 'FP03');
        $esDebito = ($codigoMetodo === 'DEBITO_BANCARIO' || str_contains($textoMetodo, 'DEBITO') || str_contains($textoMetodo, 'BANCARIO') || $codigoMetodo === 'FP04');

        if ($esTarjeta) {
            $authDebito['mostrar'] = true;
            $authDebito['tipo'] = 'TARJETA_CREDITO';
            try {
                $enc = $cliente->pagoTarjeta?->tarjeta_numero_enc;
                $authDebito['numero'] = $enc ? \Illuminate\Support\Facades\Crypt::decryptString($enc) : '';
            } catch (\Exception $e) {
                $authDebito['numero'] = 'Error al descifrar';
            }
            $authDebito['expiracion'] = $cliente->pagoTarjeta?->tarjeta_exp;
        } elseif ($esDebito) {
            $authDebito['mostrar'] = true;
            $authDebito['tipo'] = 'DEBITO_BANCARIO';
            if ($cliente->pagoCuenta) {
                $authDebito['numero'] = $cliente->pagoCuenta->cuenta_numero_enc
                    ? \Illuminate\Support\Facades\Crypt::decryptString($cliente->pagoCuenta->cuenta_numero_enc)
                    : 'Error al descifrar';

                // Banco
                if ($cliente->pagoCuenta->tipoBanco) { // tipoBanco relation uses TipoBancoVenta
                    // Assuming the relation works, otherwise access directly:
                    // But wait, the relation in VentaPagoCuenta might be 'tipoBanco' belonging to TipoBancoVenta
                    $authDebito['banco'] = $cliente->pagoCuenta->tipoBanco->nombre;
                }
                // Tipo de Cuenta (FIX: Agregado)
                $authDebito['tipo_cuenta'] = $cliente->pagoCuenta->tipo_cuenta;
            }
        }

        $this->info("Generando PDF... (Metodo: $codigoMetodo, Auth: " . ($authDebito['mostrar'] ? 'SI' : 'NO') . ")");

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('contratos.contrato_master', compact('cliente', 'plan', 'datosPrestador', 'authDebito'))->setPaper('A4');
        $pdfContent = $pdf->output();

        // Enviar Correo
        $destinatario = $emailDestino ?: ($cliente->correos[0] ?? null);

        if (!$destinatario) {
            $this->error("No hay destinatario.");
            return \Illuminate\Console\Command::FAILURE;
        }

        $this->info("Enviando correo a: $destinatario");

        \Illuminate\Support\Facades\Mail::to($destinatario)->send(new \App\Mail\ContratoSocnet($cliente, $pdfContent));

        $this->info("Correo enviado correctamente.");
        return \Illuminate\Console\Command::SUCCESS;
    }
}
