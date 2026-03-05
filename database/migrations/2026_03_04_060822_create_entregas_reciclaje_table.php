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
        Schema::create('entregas_reciclaje', function (Blueprint $table) {
            $table->id('id_entrega');
            $table->unsignedBigInteger('id_punto_verde');
            $table->unsignedBigInteger('id_material');
            $table->unsignedBigInteger('id_ciudadano');
            $table->decimal('cantidad_kg', 8, 2);
            $table->dateTime('fecha_hora');
            $table->timestamps();
            
            $table->foreign('id_punto_verde')->references('id_punto_verde')->on('puntos_verdes');
            $table->foreign('id_material')->references('id_material')->on('tipos_material');
            $table->foreign('id_ciudadano')->references('id_ciudadano')->on('ciudadanos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas_reciclaje');
    }
};
