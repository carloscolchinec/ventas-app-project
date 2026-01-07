<?php

namespace App\Http\Controllers;

use App\Models\MetodoPagoVenta;
use App\Models\TipoBancoVenta;
use App\Models\ServicioAdicionalVenta;
use Illuminate\Support\Facades\Log;

class CatalogosVentaController extends Controller
{
    // GET /api/catalogos/metodos-pago
    public function metodosPago()
    {
        $data = MetodoPagoVenta::where('activo', true)
            ->orderBy('nombre')
            ->get(['id','codigo','nombre']);


        Log::alert($data);
        return response()->json($data);
    }

    // GET /api/catalogos/tipos-banco
    public function tiposBanco()
    {
        $data = TipoBancoVenta::where('activo', true)
            ->orderBy('tipo')->orderBy('nombre')
            ->get(['id','nombre','tipo']);
        return response()->json($data);
    }

    // GET /api/catalogos/servicios-adicionales
    public function serviciosAdicionales()
    {
        $data = ServicioAdicionalVenta::where('activo', true)
            ->orderBy('nombre')
            ->get(['id','codigo','nombre','periodicidad','precio','descripcion']);
        return response()->json($data);
    }
}
