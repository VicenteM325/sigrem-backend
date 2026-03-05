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
        Schema::create('recolecciones', function (Blueprint $table) {
            $table->id('id_recoleccion');
            $table->unsignedBigInteger('id_asignacion');
            $table->dateTime('hora_inicio')->nullable();
            $table->dateTime('hora_fin')->nullable();
            $table->decimal('basura_recolectada_ton', 10, 2)->nullable();
            $table->enum('estado_recoleccion', ['programada', 'en_proceso', 'completada', 'incompleta'])->default('programada');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->foreign('id_asignacion')->references('id_asignacion')->on('asignaciones_ruta_camion')->onDelete('cascade');
            $table->unique('id_asignacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recolecciones');
    }
};
