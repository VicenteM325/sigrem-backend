<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conductors', function (Blueprint $table) {
            // Eliminar las columnas viejas
            $table->dropColumn(['telefono', 'estado']);
        });

        // Agregando las nuevas columnas en una segunda llamada para evitar conflictos
        Schema::table('conductors', function (Blueprint $table) {
            $table->date('fecha_vencimiento_licencia')->nullable()->after('licencia');
            $table->string('categoria_licencia')->nullable()->after('fecha_vencimiento_licencia');
            $table->boolean('disponible')->default(true)->after('categoria_licencia');
        });

        // Actualizar registros existentes
        \DB::table('conductors')->update([
            'fecha_vencimiento_licencia' => now()->addYear(),
            'categoria_licencia' => 'B',
        ]);

        // Hacer no nulas
        Schema::table('conductors', function (Blueprint $table) {
            $table->date('fecha_vencimiento_licencia')->nullable(false)->change();
            $table->string('categoria_licencia')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conductors', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_vencimiento_licencia',
                'categoria_licencia',
                'disponible'
            ]);
            $table->string('telefono')->nullable();
            $table->boolean('estado')->default(true);
        });
    }
};
