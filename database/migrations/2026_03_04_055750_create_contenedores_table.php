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
        Schema::create('contenedores', function (Blueprint $table) {
            $table->id('id_contenedor');
            $table->unsignedBigInteger('id_punto_verde');
            $table->unsignedBigInteger('id_material');
            $table->decimal('capacidad_m3', 8, 2);
            $table->decimal('porcentaje_llenado', 5, 2)->default(0);
            $table->enum('estado_contenedor', ['disponible', 'lleno', 'mantenimiento'])->default('disponible');
            $table->timestamps();
            
            $table->foreign('id_punto_verde')->references('id_punto_verde')->on('puntos_verdes')->onDelete('cascade');
            $table->foreign('id_material')->references('id_material')->on('tipos_material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contenedores');
    }
};
