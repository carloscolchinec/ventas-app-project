<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('servicios_adicionales_ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 60)->unique();           // ZAPPING_TV, WIFI_EXTENDER_HC220, UPS_FORZA_DC140USB, CAMARA_EZVIZ_360G
            $table->string('nombre', 180);
            $table->enum('periodicidad', ['MENSUAL','UNICO'])->default('MENSUAL');
            $table->decimal('precio', 10, 2)->default(0);
            $table->string('descripcion', 255)->nullable();   // "por 6 meses", "PROXIMAMENTE", etc.
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('servicios_adicionales_ventas');
    }
};
