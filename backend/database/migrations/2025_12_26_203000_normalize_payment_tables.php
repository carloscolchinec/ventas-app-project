<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Crear tabla para tarjetas
        Schema::create('ventas_pagos_tarjetas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id');
            $table->text('tarjeta_numero_enc')->nullable();
            $table->string('tarjeta_last4')->nullable();
            $table->string('tarjeta_exp')->nullable();
            $table->timestamps();

            $table->foreign('venta_id')->references('id')->on('ventas_clientes_cedula')->onDelete('cascade');
        });

        // 2. Crear tabla para cuentas bancarias
        Schema::create('ventas_pagos_cuentas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id');
            $table->unsignedBigInteger('tipo_banco_id')->nullable();
            $table->text('cuenta_numero_enc')->nullable();
            $table->string('tipo_cuenta')->nullable(); // Ahorros/Corriente
            $table->timestamps();

            $table->foreign('venta_id')->references('id')->on('ventas_clientes_cedula')->onDelete('cascade');
            $table->foreign('tipo_banco_id')->references('id')->on('tipo_banco_ventas')->onDelete('set null');
        });

        // 3. Migrar datos existentes
        $ventas = DB::table('ventas_clientes_cedula')->get();

        foreach ($ventas as $venta) {
            // Si tiene datos de tarjeta
            if ($venta->tarjeta_last4 || $venta->tarjeta_numero_enc) {
                DB::table('ventas_pagos_tarjetas')->insert([
                    'venta_id' => $venta->id,
                    'tarjeta_numero_enc' => $venta->tarjeta_numero_enc,
                    'tarjeta_last4' => $venta->tarjeta_last4,
                    'tarjeta_exp' => $venta->tarjeta_exp,
                    'created_at' => $venta->created_at,
                    'updated_at' => $venta->updated_at,
                ]);
            }

            // Si tiene datos de cuenta
            if ($venta->tipo_banco_id || $venta->cuenta_numero_enc) {
                DB::table('ventas_pagos_cuentas')->insert([
                    'venta_id' => $venta->id,
                    'tipo_banco_id' => $venta->tipo_banco_id,
                    'cuenta_numero_enc' => $venta->cuenta_numero_enc,
                    'tipo_cuenta' => $venta->tipo_cuenta, // Mover tipo_cuenta aquí
                    'created_at' => $venta->created_at,
                    'updated_at' => $venta->updated_at,
                ]);
            }
        }

        // 4. Eliminar columnas de la tabla principal
        Schema::table('ventas_clientes_cedula', function (Blueprint $table) {
            $table->dropForeign(['tipo_banco_id']); // Drop FK first if exists
            $table->dropColumn([
                'tipo_banco_id',
                'tarjeta_last4',
                'tarjeta_exp',
                'tarjeta_numero_enc',
                'cuenta_numero_enc',
                'tipo_cuenta', // Se movio a la tabla cuentas
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 1. Restaurar columnas
        Schema::table('ventas_clientes_cedula', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_banco_id')->nullable();
            $table->text('tarjeta_numero_enc')->nullable();
            $table->string('tarjeta_last4')->nullable();
            $table->string('tarjeta_exp')->nullable();
            $table->text('cuenta_numero_enc')->nullable();
            $table->string('tipo_cuenta')->nullable();

            $table->foreign('tipo_banco_id')->references('id')->on('tipo_banco_ventas')->onDelete('set null');
        });

        // 2. Restaurar datos (Opcional, complejo revertir perfectamente si hubo cambios posteriores)
        // Por simplicidad, en down a veces no se migra todo de vuelta, pero intentemos lo básico.
        $tarjetas = DB::table('ventas_pagos_tarjetas')->get();
        foreach ($tarjetas as $t) {
            DB::table('ventas_clientes_cedula')
                ->where('id', $t->venta_id)
                ->update([
                        'tarjeta_numero_enc' => $t->tarjeta_numero_enc,
                        'tarjeta_last4' => $t->tarjeta_last4,
                        'tarjeta_exp' => $t->tarjeta_exp
                    ]);
        }

        $cuentas = DB::table('ventas_pagos_cuentas')->get();
        foreach ($cuentas as $c) {
            DB::table('ventas_clientes_cedula')
                ->where('id', $c->venta_id)
                ->update([
                        'tipo_banco_id' => $c->tipo_banco_id,
                        'cuenta_numero_enc' => $c->cuenta_numero_enc,
                        'tipo_cuenta' => $c->tipo_cuenta
                    ]);
        }

        // 3. Eliminar tablas
        Schema::dropIfExists('ventas_pagos_tarjetas');
        Schema::dropIfExists('ventas_pagos_cuentas');
    }
};
