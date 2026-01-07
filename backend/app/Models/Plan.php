<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';
    protected $primaryKey = 'id_plan';
    public $timestamps = false;

    protected $fillable = [
        'id_router',
        'nombre_plan',
        'descripcion_plan',
        'id_ippool',
        'mb_subida',
        'mb_bajada',
        'precio',
        'nivel_comparticion',
        'tipo_red',
        'estado',
        'id_usuario_auditor',
    ];

    /* ===================== Relaciones ===================== */

    public function router()
    {
        return $this->belongsTo(Router::class, 'id_router', 'id_router');
    }

    public function usuarioAuditor()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_auditor');
    }

    public function ippool()
    {
        return $this->belongsTo(IpPool::class, 'id_ippool', 'id_ippool');
    }

    /** N:M con métodos de pago (respeta pivot y método activo) */
    public function metodos()
    {
        return $this->belongsToMany(
            MetodoPagoVenta::class,
            'planes_metodos_pagos',
            'id_plan',
            'id_metodo'
        )
            ->withPivot(['estado'])
            ->wherePivot('estado', 'A')
            ->where('metodos_pagos_ventas.activo', 1);
    }

    /* ===================== Scopes útiles ===================== */

    /** Solo planes activos */
    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('estado', 'A');
    }

    /** Filtra por método de pago (FP01..FP04) respetando la relación */
    public function scopeConMetodo(Builder $q, string $codigo): Builder
    {
        $codigo = strtoupper($codigo);
        return $q->whereHas('metodos', function ($m) use ($codigo) {
            $m->where('metodos_pagos_ventas.codigo', $codigo)
                ->where('metodos_pagos_ventas.activo', 1)
                ->where('planes_metodos_pagos.estado', 'A');
        });
    }

    /** Helpers LIKE */
    public function scopeLike(Builder $q, string $txt): Builder
    {
        return $q->where('nombre_plan', 'LIKE', $txt);
    }
    public function scopeNotLike(Builder $q, string $txt): Builder
    {
        return $q->where('nombre_plan', 'NOT LIKE', $txt);
    }

    public function scopeSoportaMetodo(Builder $q, string $codigo): Builder
    {
        return $q->whereHas('metodos', function ($m) use ($codigo) {
            $m->where('metodos_pagos_ventas.codigo', $codigo)
                ->where('metodos_pagos_ventas.activo', 1);
        });
    }


    /** Segmentos por nombre (según tu data actual) */
    public function scopeCiudadano(Builder $q): Builder
    {
        return $q->like('%CIUDADANO%');
    }
    public function scopeNoCiudadano(Builder $q): Builder
    {
        return $q->notLike('%CIUDADANO%');
    }
    public function scopePromoBienvenida(Builder $q): Builder
    {
        return $q->like('%PROMO BIENVENIDA%');
    }
    public function scopePromoFlash(Builder $q): Builder
    {
        return $q->like('%PROMO FLASH%');
    }
    public function scopeBeneficioDoble(Builder $q): Builder
    {
        return $q->like('%BENEFICIO DOBLE%');
    }
    public function scopeConZapping(Builder $q): Builder
    {
        return $q->like('%ZAPPING%');
    }
    public function scopeSinZapping(Builder $q): Builder
    {
        return $q->notLike('%ZAPPING%');
    }

    /** “Normales”: no promos y no ciudadano */
    public function scopeNormales(Builder $q): Builder
    {
        return $q->notLike('%PROMO BIENVENIDA%')
            ->notLike('%PROMO FLASH%')
            ->notLike('%BENEFICIO DOBLE%')
            ->noCiudadano();
    }
}
