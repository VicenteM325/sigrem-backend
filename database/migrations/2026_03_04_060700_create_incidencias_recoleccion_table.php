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
        Schema::create('incidencias_recoleccion', function (Blueprint $table) {
            $table->id('id_incidencia');
            $table->unsignedBigInteger('id_recoleccion');
            $table->text('descripcion');
            $table->dateTime('fecha');
            $table->enum('tipo_incidencia', ['camion_descompuesto', 'bloqueo_via', 'exceso_basura', 'problema_seguridad', 'otro'])->default('otro');
            $table->timestamps();
            
            $table->foreign('id_recoleccion')->references('id_recoleccion')->on('recolecciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias_recoleccion');
    }
};
