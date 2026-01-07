<?php

namespace App\Http\Controllers;

use App\Libraries\RouterosAPI;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\UsuarioCliente;
use App\Models\InformacionAdicionalCliente;
use App\Models\AccionRealizada;
use App\Models\AsignacionServicioCliente;
use App\Models\IpPool;
use App\Models\Olt;
use App\Models\Ont;
use App\Models\Plan;
use App\Models\Router;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use phpseclib3\Net\SSH2;

class DashboardController extends Controller
{

    public function obtenerResumen()
    {
        // Obtener la cantidad de clientes con el rol CLIENTE (id_rol = 4) y con estado Activo/Inactivo
        $cantidadClientes = Cliente::whereIn('estado', ['A', 'I'])
            ->whereHas('usuariosClientes.usuario', function ($query) {
                $query->where('id_rol', 4); // Filtrar solo clientes
            })
            ->count();
    
        // Cantidad de planes activos
        $cantidadPlanes = Plan::where('estado', 'A')->count();
    
        // Cantidad de IPs disponibles en la tabla de pools de IPs
        $cantidadIps = IpPool::count();
    
        // Cantidad de routers activos
        $cantidadRouters = Router::where('estado', 'A')->count();
    
        // Cantidad de OLTs activas
        $cantidadOlts = Olt::where('estado', 'A')->count();
    
        // Cantidad total de ONTs registradas en la tabla
        $totalOnts = Ont::count();
    
        // Cantidad de ONTs en uso (aquellas que están asignadas a clientes)
        $ontsEnUso = AsignacionServicioCliente::whereNotNull('id_ont') // Filtra solo las ONTs asignadas
            ->where('estado', 'A') // Solo las ONTs activas en la asignación
            ->count();
    
        // ONTs libres (total de ONTs menos las asignadas en uso)
        $ontsLibres = $totalOnts - $ontsEnUso;
    
        return response()->json([
            'success' => true,
            'data' => [
                'cantidad_clientes' => $cantidadClientes,
                'cantidad_planes' => $cantidadPlanes,
                'cantidad_ips' => $cantidadIps,
                'cantidad_routers' => $cantidadRouters,
                'cantidad_olts' => $cantidadOlts,
                'onts_libres' => max($ontsLibres, 0), // Asegurar que no haya negativos
                'onts_en_uso' => $ontsEnUso,
            ]
        ], 200);
    }
    
}
