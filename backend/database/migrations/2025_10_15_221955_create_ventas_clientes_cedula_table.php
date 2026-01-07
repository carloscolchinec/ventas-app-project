<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentasClientesCedulaTable extends Migration
{
    public function up()
    {
        Schema::create('ventas_clientes_cedula', function (Blueprint $table) {
            $table->id();

            $table->string('tipo_documento', 20);
            $table->string('serie_contrato', 50);
            $table->string('identificacion', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->date('fecha_nacimiento');

            $table->boolean('es_tercera_edad')->default(false);
            $table->boolean('es_presenta_discapacidad')->default(false);

            $table->string('direccion', 255);
            $table->string('referencia_domiciliaria', 255)->nullable();
            $table->string('ciudad', 100);
            $table->string('provincia', 100);

            $table->string('latitud')->nullable();
            $table->string('longitud')->nullable();

            $table->json('telefonos');
            $table->json('correos');

            $table->string('establecimiento', 100);
            $table->string('tipo_cuenta', 50);                 // Residencial | Corporativa | Otro
            $table->string('tipo_cuenta_otro', 100)->nullable();

            $table->string('plan', 255);
            $table->string('red_acceso', 100);
            $table->string('nivel_comparticion', 10);
            $table->string('dias_gratis', 10)->default('0');

            // Archivos
            $table->string('cedula_frontal')->nullable();
            $table->string('cedula_trasera')->nullable();
            $table->string('planilla_luz')->nullable();
            $table->string('firma')->nullable();

            // Estado
            $table->string('estado', 20)->default('VI');

            // Usuario que registra
            $table->unsignedBigInteger('id_usuario_registro')->nullable();
            $table->foreign('id_usuario_registro')
                ->references('id_usuario')
                ->on('usuarios')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // ====== Pago (FKs + datos) ======
            $table->foreignId('metodo_pago_id')
                ->constrained('metodos_pagos_ventas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('tipo_banco_id')                 // solo si "Débito Bancario"
                ->nullable()
                ->constrained('tipo_banco_ventas')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // Tarjeta (NO CVV)
            $table->string('tarjeta_last4', 4)->nullable();    // últimos 4
            $table->string('tarjeta_exp', 5)->nullable();      // MM/AA
            $table->text('tarjeta_numero_enc')->nullable();    // número encriptado

            // Débito bancario (número de cuenta encriptado)
            $table->text('cuenta_numero_enc')->nullable();

            // (opcional) redundancia para reportes rápidos
            $table->string('metodo_pago_texto', 100)->nullable();

            $table->timestamps();

            // Índices útiles
            $table->index(['ciudad', 'provincia']);
            $table->index(['estado']);
            $table->index(['metodo_pago_id']);
            $table->index(['tipo_banco_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventas_clientes_cedula');
    }
}
