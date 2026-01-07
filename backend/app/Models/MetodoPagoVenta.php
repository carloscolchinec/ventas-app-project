<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MetodoPagoVenta extends Model
{
    protected $table = 'metodos_pagos_ventas';
    protected $fillable = ['codigo','nombre','activo'];
    protected $casts = ['activo' => 'bool'];

    // Relación con ventas (ya la tenías)
    public function ventas()
    {
        return $this->hasMany(VentasClienteCedula::class, 'metodo_pago_id');
    }

    // Relación N:M hacia planes
    public function planes()
    {
        return $this->belongsToMany(Plan::class, 'planes_metodos_pagos', 'id_metodo', 'id_plan')
            ->withPivot(['estado']);
    }

    /* Scopes de conveniencia */
    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('activo', true);
    }

    public function scopeCodigo(Builder $q, string $codigo): Builder
    {
        return $q->where('codigo', strtoupper($codigo));
    }
}
