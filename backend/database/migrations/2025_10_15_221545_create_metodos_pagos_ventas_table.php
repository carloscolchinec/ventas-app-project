<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('metodos_pagos_ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();   // EFECTIVO, TRANSFERENCIA, TARJETA_CREDITO, DEBITO_BANCARIO
            $table->string('nombre', 100);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('metodos_pagos_ventas');
    }
};
