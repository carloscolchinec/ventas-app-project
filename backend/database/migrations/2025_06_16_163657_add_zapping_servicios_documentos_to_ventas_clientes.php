<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas_clientes', function (Blueprint $table) {
            $table->boolean('zapping_tv')->default(false);
            $table->json('servicio_adicionales')->nullable();

            $table->string('cedula_frontal')->nullable();
            $table->string('cedula_trasera')->nullable();
            $table->string('planilla_luz')->nullable();
        });
    }

    public function down()
    {
        Schema::table('ventas_clientes', function (Blueprint $table) {
            $table->dropColumn([
                'zapping_tv',
                'servicio_adicionales',
                'cedula_frontal',
                'cedula_trasera',
                'planilla_luz'
            ]);
        });
    }
};
