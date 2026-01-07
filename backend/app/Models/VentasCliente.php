<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentasCliente extends Model
{
    protected $table = 'ventas_clientes';
    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'codigo_contrato',
        'tipo_documento',
        'identificacion',
        'nombres',
        'apellidos',
        'razon_social',
        'fecha_nacimiento',
        'direccion',
        'referencia_domiciliaria',
        'ciudad',
        'provincia',
        'latitud',
        'longitud',
        'telefonos',
        'correo',
        'plan',
        'dias_gratis',
        'imagenes',
        'firma',
        'zapping_tv',
        'servicio_adicionales',
        'cedula_frontal',
        'cedula_trasera',
        'planilla_luz',
        'id_usuario_registro',
        'estado',
    ];

    protected $casts = [
        'telefonos' => 'array',
        'imagenes' => 'array',
        'servicio_adicionales' => 'array',
        'zapping_tv' => 'boolean',
    ];
}
