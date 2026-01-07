<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('servicio_adicional_venta', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venta_id')
                ->constrained('ventas_clientes_cedula')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('servicio_adicional_id')
                ->constrained('servicios_adicionales_ventas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('total', 10, 2)->default(0); // precio_unitario * cantidad

            $table->timestamps();

            $table->unique(['venta_id','servicio_adicional_id'], 'venta_servicio_unq');
            $table->index(['servicio_adicional_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('servicio_adicional_venta');
    }
};
