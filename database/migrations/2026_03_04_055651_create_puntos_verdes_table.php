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
        Schema::create('puntos_verdes', function (Blueprint $table) {
            $table->id('id_punto_verde');
            $table->unsignedBigInteger('id_zona');
            $table->string('nombre', 100);
            $table->string('direccion', 255);
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->decimal('capacidad_total_m3', 10, 2);
            $table->string('horario_atencion', 100);
            $table->string('encargado', 100);
            $table->timestamps();
            
            $table->foreign('id_zona')->references('id_zona')->on('zonas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_verdes');
    }
};
