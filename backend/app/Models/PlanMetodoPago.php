<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanMetodoPago extends Model
{
    protected $table = 'planes_metodos_pagos';
    protected $primaryKey = 'id';
    public $timestamps = false; // usa fecha_creacion/fecha_actualizacion

    protected $fillable = [
        'id_plan',
        'id_metodo',
        'estado',              // 'A'/'I'
        'fecha_creacion',
        'fecha_actualizacion',
        'id_usuario_auditor',
    ];
}
