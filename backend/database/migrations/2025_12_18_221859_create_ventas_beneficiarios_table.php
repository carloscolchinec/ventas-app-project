<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas_beneficiarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venta_id')
                ->constrained('ventas_clientes_cedula')
                ->onDelete('cascade');

            $table->string('identificacion', 20);
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->decimal('porcentaje', 5, 2);

            // Documentos del beneficiario
            $table->string('cedula_frontal')->nullable();
            $table->string('cedula_trasera')->nullable();
            $table->string('carnet')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventas_beneficiarios');
    }
};
