<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaBeneficiario extends Model
{
    use HasFactory;

    protected $table = 'ventas_beneficiarios';

    protected $fillable = [
        'venta_id',
        'identificacion',
        'nombres',
        'apellidos',
        'porcentaje',
        'cedula_frontal',
        'cedula_trasera',
        'carnet',
    ];

    public function venta()
    {
        return $this->belongsTo(VentasClienteCedula::class, 'venta_id');
    }
}
