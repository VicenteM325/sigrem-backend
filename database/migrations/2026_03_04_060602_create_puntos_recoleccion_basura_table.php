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
        Schema::create('puntos_recoleccion_basura', function (Blueprint $table) {
            $table->id('id_punto_basura');
            $table->unsignedBigInteger('id_recoleccion');
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->decimal('volumen_estimado_kg', 8, 2);
            $table->enum('estado_recoleccion', ['pendiente', 'recolectado', 'problema'])->default('pendiente');
            $table->timestamps();
            
            $table->foreign('id_recoleccion')->references('id_recoleccion')->on('recolecciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_recoleccion_basura');
    }
};
