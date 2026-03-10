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
        Schema::create('dias_recoleccion', function (Blueprint $table) {
            $table->id('id_dia');
            $table->unsignedBigInteger('id_ruta');
            $table->enum('nombre_dia', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']);
            $table->timestamps();
            
            $table->foreign('id_ruta')->references('id_ruta')->on('rutas')->onDelete('cascade');
            $table->unique(['id_ruta', 'nombre_dia']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias_recoleccion');
    }
};
