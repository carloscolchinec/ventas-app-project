<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas API para tu aplicación. Estas rutas están
| cargadas por el RouteServiceProvider dentro del grupo asignado con el
| middleware "api".
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogosVentaController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IpPoolController;
use App\Http\Controllers\OLTController;
use App\Http\Controllers\OntController;
use App\Http\Controllers\OnuController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\VentasClienteController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// Rutas públicas (sin autenticación)
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth:api', 'handle.token.expiration'])->group(function () {
    // Rutas de usuario
    Route::get('/user/me', [AuthController::class, 'me']);

    // Rutas de registros (logs)
    Route::get('/obtener-acciones-realizadas', [LogController::class, 'obtenerRegistros']);

    // Rutas para la gestión de clientes
    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClientesController::class, 'listarClientes']); // Listar clientes
        Route::post('/verificar-correo', [ClientesController::class, 'verificarCorreo']);
        Route::post('/', [ClientesController::class, 'guardarCliente']); // Crear cliente
        Route::get('/{id}', [ClientesController::class, 'MostrarInformacionCliente']); // Mostrar cliente específico
        Route::put('/{id}', [ClientesController::class, 'ActualizarCliente']); // Actualizar cliente
        Route::delete('/{id}', [ClientesController::class, 'EliminarCliente']); // Eliminar cliente (cambia estado a "E")
        Route::post('/{id}/reparar', [ClientesController::class, 'repararCliente']);
    });

    Route::prefix('routers')->group(function () {
        Route::get('/', [RouterController::class, 'listarRouters']);
        Route::post('/verificar-conexion', [RouterController::class, 'VerificarConexionRouter']);
        Route::post('/obtener-router', [RouterController::class, 'obtenerRouter']);
        Route::post('/', [RouterController::class, 'GuardarRouter']);
        Route::put('/{id}', [RouterController::class, 'ActualizarRouter']);
        Route::delete('/{id}', [RouterController::class, 'EliminarRouter']);
    });

    Route::prefix('planes')->group(function () {
        Route::get('/', [PlanController::class, 'listarPlanes']); // Listar planes
        Route::get('/filtrar', [VentasClienteController::class, 'buscarPorFiltroPlan']);
        Route::post('/crear', [PlanController::class, 'crearPlan']); // Crear plan
        Route::put('/{id}', [PlanController::class, 'actualizarPlan']); // Actualizar plan
        Route::delete('/{id}', [PlanController::class, 'eliminarPlan']); // Eliminar plan
        Route::put('/reparar/{id}', [PlanController::class, 'repararPlanMikroTik']);
    });


    Route::prefix('ippools')->group(function () {
        Route::get('/verificar', [IpPoolController::class, 'verificarIPPoolsEnMikrotik']); // 👈 Mover antes
        Route::get('/', [IpPoolController::class, 'listarIPPool']); // Listar IP Pools
        Route::post('/', [IpPoolController::class, 'guardarIPPool']); // Crear IP Pool
        Route::get('/{id}', [IpPoolController::class, 'mostrarIPPool']); // Mostrar IP Pool específico
        Route::put('/{id}', [IpPoolController::class, 'actualizarIPPool']); // Actualizar IP Pool
        Route::post('/importar', [IpPoolController::class, 'importarIPPoolsDesdeMikrotik']); // Importar pools
        Route::delete('/{id}', [IpPoolController::class, 'eliminarIPPool']); // Eliminar IP Pool
        Route::post('/reparar/{id}', [IpPoolController::class, 'repararIPPool']);
    });

    Route::prefix('olts')->group(function () {
        Route::post('/obtener', [OLTController::class, 'obtenerOLTs']);
        Route::get('/obtener-olt/{id_olt}', [OLTController::class, 'obtenerOLT']);
        Route::post('/crear', [OLTController::class, 'crearOLT']);
        Route::post('/actualizar', [OLTController::class, 'actualizarOLT']);
        Route::post('/eliminar', [OLTController::class, 'eliminarOLT']);
    });

    Route::prefix('onts')->group(function () {
        Route::get('/obtener', [OntController::class, 'obtenerOnts']); // Obtener todas las ONUs
        Route::get('/obtener/{id_onu}', [OntController::class, 'mostrarOnt']); // Obtener ONU específica
        Route::post('/crear', [OntController::class, 'guardarOnt']); // Crear o reactivar ONU
        Route::put('/actualizar', [OntController::class, 'actualizarOnt']); // Actualizar ONU
        Route::delete('/eliminar', [OntController::class, 'eliminarOnt']); // Eliminar ONU
    });

    Route::prefix('catalogos')->group(function () {
        Route::get('/metodos-pago', [CatalogosVentaController::class, 'metodosPago']);
        Route::get('/tipos-banco', [CatalogosVentaController::class, 'tiposBanco']);
        Route::get('/servicios-adicionales', [CatalogosVentaController::class, 'serviciosAdicionales']);
    });


    Route::prefix('ventas')->group(function () {
        Route::post('/', [VentasClienteController::class, 'store']); // Crear contrato de venta
        Route::post('/{id}/imagenes', [VentasClienteController::class, 'uploadImagenes']);
        // Route::put('/{id}/imagenes', [VentasClienteController::class, 'updateImagenes']); // Agregar más imágenes
        Route::get('/', [VentasClienteController::class, 'index']); // Listar ventas por usuario
        Route::get('/estadisticas', [VentasClienteController::class, 'estadisticas']); // NUEVA: Estadísticas
        Route::get('/feed', [VentasClienteController::class, 'feedNotificaciones']);

        Route::get('/comisiones', [VentasClienteController::class, 'comisiones']);
        // Route::prefix('comisiones')->group(function () {
        // Último ciclo cerrado (mes vencido)
        Route::get('/mes-vencido', [VentasClienteController::class, 'comisionesMesVencido']);

        // Ciclo que TERMINA el 27 de {anio}-{mes} (mes en 2 dígitos)
        Route::get('/{anio}/{mes}', [VentasClienteController::class, 'comisionesPorMes'])
            ->where(['anio' => '\d{4}', 'mes' => '0[1-9]|1[0-2]']);

        // Historial de últimos N ciclos (por defecto 6)
        Route::get('/historial', [VentasClienteController::class, 'comisionesHistorial']);


        Route::get('/verificar-identificacion', [VentasClienteController::class, 'verificarIdentificacion']);
        Route::get('/{id}', [VentasClienteController::class, 'show']); // Ver detalles
        Route::get('/{id}/mostrar-contrato', [VentasClienteController::class, 'mostrarContrato']);
        // });
    });



    Route::prefix('dashboard')->group(function () {
        Route::get('/obtener', [DashboardController::class, 'obtenerResumen']); // Obtener todas las ONUs

    });
});

Route::get('/contrato/{id}', [VentasClienteController::class, 'mostrarContrato']);


Route::get('onu-autofind', [OLTController::class, 'registerONT']);

Route::get('version/seroficomapp', function () {
    return response()->json([
        'app' => 'seroficomapp',
        'version' => '1.1.0',     // <-- cámbialo cuando publiques
        'build' => 100,         // <-- opcional: número de build
        'env' => app()->environment(),
        'time' => now()->toIso8601String(),
    ]);
});


// Route::prefix('olt')->group(function () {
//     Route::post('/ont/power', [OLTController::class, 'getOntPower']);
// });