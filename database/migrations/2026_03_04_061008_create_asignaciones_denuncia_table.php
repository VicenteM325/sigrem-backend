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
        Schema::create('asignaciones_denuncia', function (Blueprint $table) {
            $table->id('id_asignacion_denuncia');
            $table->unsignedBigInteger('id_denuncia');
            $table->unsignedBigInteger('id_cuadrilla');
            $table->date('fecha_programada');
            $table->text('recursos_estimados')->nullable();
            $table->enum('estado', ['asignada', 'en_proceso', 'completada', 'cancelada'])->default('asignada');
            $table->timestamps();
            
            $table->foreign('id_denuncia')->references('id_denuncia')->on('denuncias')->onDelete('cascade');
            $table->foreign('id_cuadrilla')->references('id_cuadrilla')->on('cuadrillas_limpieza');
            $table->unique(['id_denuncia', 'fecha_programada'], 'denuncia_fecha_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_denuncia');
    }
};
