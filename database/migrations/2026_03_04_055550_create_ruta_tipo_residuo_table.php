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
        Schema::create('ruta_tipo_residuo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ruta');
            $table->unsignedBigInteger('id_tipo_residuo');
            $table->timestamps();
            
            $table->foreign('id_ruta')->references('id_ruta')->on('rutas')->onDelete('cascade');
            $table->foreign('id_tipo_residuo')->references('id_tipo_residuo')->on('tipos_residuo')->onDelete('cascade');
            $table->unique(['id_ruta', 'id_tipo_residuo'], 'ruta_tipo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruta_tipo_residuo');
    }
};
