<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoBancoVenta extends Model
{
    protected $table = 'tipo_banco_ventas';
    protected $fillable = ['nombre','tipo','activo'];
    protected $casts = ['activo' => 'bool'];

    public function ventas()
    {
        return $this->hasMany(VentasClienteCedula::class, 'tipo_banco_id');
    }
}
