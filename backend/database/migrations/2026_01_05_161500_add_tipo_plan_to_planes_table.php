<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->enum('tipo_plan', ['RES', 'CORP', 'PYME'])
                ->default('RES')
                ->after('nombre_plan');
        });

        // Auto-update existing records based on name
        DB::statement("UPDATE planes SET tipo_plan = 'CORP' WHERE nombre_plan LIKE '%CORP%'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn('tipo_plan');
        });
    }
};
