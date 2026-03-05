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
        Schema::create('puntos_ruta', function (Blueprint $table) {
            $table->id('id_punto_ruta');
            $table->unsignedBigInteger('id_ruta');
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->integer('orden');
            $table->timestamps();
            
            $table->foreign('id_ruta')->references('id_ruta')->on('rutas')->onDelete('cascade');
            $table->unique(['id_ruta', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_ruta');
    }
};
