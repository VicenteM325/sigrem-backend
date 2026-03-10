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
        Schema::create('cuadrillas_limpieza', function (Blueprint $table) {
            $table->id('id_cuadrilla');
            $table->string('nombre_cuadrilla', 100);
            $table->string('responsable', 100);
            $table->enum('estado', ['disponible', 'asignada', 'descanso'])->default('disponible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuadrillas_limpieza');
    }
};
