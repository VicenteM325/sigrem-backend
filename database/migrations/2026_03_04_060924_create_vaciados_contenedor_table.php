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
        Schema::create('vaciados_contenedor', function (Blueprint $table) {
            $table->id('id_vaciado');
            $table->unsignedBigInteger('id_contenedor');
            $table->date('fecha_programada');
            $table->date('fecha_real')->nullable();
            $table->enum('estado', ['programado', 'realizado', 'cancelado'])->default('programado');
            $table->timestamps();
            
            $table->foreign('id_contenedor')->references('id_contenedor')->on('contenedores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaciados_contenedor');
    }
};
