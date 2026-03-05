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
        Schema::create('asignaciones_ruta_camion', function (Blueprint $table) {
            $table->id('id_asignacion');
            $table->unsignedBigInteger('id_ruta');
            $table->unsignedBigInteger('id_camion');
            $table->date('fecha_programada');
            $table->enum('estado', ['programada', 'en_proceso', 'completada', 'cancelada'])->default('programada');
            $table->decimal('total_estimado_kg', 10, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('id_ruta')->references('id_ruta')->on('rutas');
            $table->foreign('id_camion')->references('id_camion')->on('camiones');
            $table->unique(['id_ruta', 'fecha_programada'], 'ruta_fecha_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_ruta_camion');
    }
};
