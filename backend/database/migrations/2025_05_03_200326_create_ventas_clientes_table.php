<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentasClientesTable extends Migration
{
    public function up()
    {
        Schema::create('ventas_clientes', function (Blueprint $table) {
            $table->id('id_venta');

            $table->string('codigo_contrato')->unique(); // COT-1234567890-030525
            $table->enum('tipo_documento', ['cedula', 'ruc', 'pasaporte'])->default('cedula');
            $table->string('identificacion', 20);

            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->string('razon_social')->nullable();
            $table->date('fecha_nacimiento')->nullable();

            $table->string('direccion');
            $table->string('referencia_domiciliaria')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('provincia')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();

            $table->json('telefonos');
            $table->string('correo')->nullable();
            $table->string('plan')->nullable();
            $table->integer('dias_gratis')->default(7);

            $table->json('imagenes'); // Lista de imágenes editables
            $table->text('firma');    // base64 o URL

            $table->unsignedBigInteger('id_usuario_registro')->nullable();
            $table->enum('estado', ['A', 'I', 'E'])->default('A');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventas_clientes');
    }
}
