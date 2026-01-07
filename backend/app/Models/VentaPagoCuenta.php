<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaPagoCuenta extends Model
{
    use HasFactory;

    protected $table = 'ventas_pagos_cuentas';

    protected $fillable = [
        'venta_id',
        'tipo_banco_id',
        'cuenta_numero_enc',
        'tipo_cuenta', // Ahorros/Corriente
    ];

    public function tipoBanco()
    {
        return $this->belongsTo(TipoBancoVenta::class, 'tipo_banco_id');
    }

    public function venta()
    {
        return $this->belongsTo(VentasClienteCedula::class, 'venta_id');
    }
    protected $appends = ['cuenta_masked'];

    public function getCuentaMaskedAttribute()
    {
        try {
            if (!$this->cuenta_numero_enc)
                return 'N/A';
            $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($this->cuenta_numero_enc);
            if (strlen($decrypted) > 4) {
                return '**** ' . substr($decrypted, -4);
            }
            return '**** ' . $decrypted;
        } catch (\Exception $e) {
            return 'ERROR';
        }
    }
}
