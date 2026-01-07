<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioAdicionalVenta extends Model
{
    protected $table = 'servicios_adicionales_ventas';
    protected $fillable = ['codigo','nombre','periodicidad','precio','descripcion','activo'];
    protected $casts = ['precio' => 'decimal:2', 'activo' => 'bool'];

    public function ventas()
    {
        return $this->belongsToMany(
            VentasClienteCedula::class,
            'servicio_adicional_venta',
            'servicio_adicional_id',
            'venta_id'
        )->withPivot(['precio_unitario','cantidad','total'])->withTimestamps();
    }
}
