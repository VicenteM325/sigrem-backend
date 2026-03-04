<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Evitar duplicados para no tener conflictos al cambiar
        $duplicados = DB::table('conductors')
            ->select('licencia', DB::raw('COUNT(*) as total'))
            ->groupBy('licencia')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $duplicado) {
            $registros = DB::table('conductors')
                ->where('licencia', $duplicado->licencia)
                ->orderBy('id_conductor')
                ->get();

            $primero = true;
            foreach ($registros as $registro) {
                if ($primero) {
                    $primero = false;
                    continue;
                }
                DB::table('conductors')
                    ->where('id_conductor', $registro->id_conductor)
                    ->update([
                        'licencia' => $registro->licencia . '-' . $registro->id_conductor
                    ]);
            }
        }

        Schema::table('conductors', function (Blueprint $table) {
            $table->unique('licencia', 'conductors_licencia_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conductors', function (Blueprint $table) {
            $table->dropUnique('conductors_licencia_unique');
        });
    }
};