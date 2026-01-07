<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaPagoTarjeta extends Model
{
    use HasFactory;

    protected $table = 'ventas_pagos_tarjetas';

    protected $fillable = [
        'venta_id',
        'tarjeta_numero_enc',
        'tarjeta_last4',
        'tarjeta_exp',
    ];

    // Relación inversa
    public function venta()
    {
        return $this->belongsTo(VentasClienteCedula::class, 'venta_id');
    }
}
