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
        Schema::table('ventas_clientes_cedula', function (Blueprint $table) {
            $table->integer('dia_pago')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventas_clientes_cedula', function (Blueprint $table) {
            $table->tinyInteger('dia_pago')->nullable()->after('metodo_pago_texto');
        });
    }
};
