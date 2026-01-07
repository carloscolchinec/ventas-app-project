<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentasClienteCedula extends Model
{
    protected $table = 'ventas_clientes_cedula';

    protected $fillable = [
        'tipo_documento',
        'serie_contrato',
        'identificacion',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'es_tercera_edad',
        'es_presenta_discapacidad',
        'direccion',
        'referencia_domiciliaria',
        'ciudad',
        'provincia',
        'latitud',
        'longitud',
        'telefonos',
        'correos',
        'establecimiento',
        'tipo_cuenta_otro',
        'plan',
        'red_acceso',
        'nivel_comparticion',
        'dias_gratis',
        'cedula_frontal',
        'cedula_trasera',
        'planilla_luz',
        'firma',
        'estado',
        'id_usuario_registro',
        // pago
        'metodo_pago_id',
        'metodo_pago_texto',
        'dia_pago',
        // 'tipo_cuenta', // MOVIDO A TABLA CUENTAS
    ];

    protected $casts = [
        'telefonos' => 'array',
        'correos' => 'array',
        'es_tercera_edad' => 'bool',
        'es_presenta_discapacidad' => 'bool',
    ];

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPagoVenta::class, 'metodo_pago_id');
    }

    // RELACIONES NUEVAS
    public function pagoTarjeta()
    {
        return $this->hasOne(VentaPagoTarjeta::class, 'venta_id');
    }

    public function pagoCuenta()
    {
        return $this->hasOne(VentaPagoCuenta::class, 'venta_id');
    }

    // Atajo para acceder al banco (opcional)
    public function getTipoBancoAttribute()
    {
        return $this->pagoCuenta?->tipoBanco;
    }

    public function historial()
    {
        return $this->hasMany(VentasHistorial::class, 'id_venta');
    }

    public function beneficiario()
    {
        return $this->hasOne(VentaBeneficiario::class, 'venta_id');
    }

    public function serviciosAdicionales()
    {
        return $this->belongsToMany(
            ServicioAdicionalVenta::class,
            'servicio_adicional_venta',
            'venta_id',
            'servicio_adicional_id'
        )->withPivot(['precio_unitario', 'cantidad', 'total'])->withTimestamps();
    }

    public function planEntity()
    {
        return $this->belongsTo(Plan::class, 'plan', 'nombre_plan');
    }
}
