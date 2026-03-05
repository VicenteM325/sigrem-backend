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
        Schema::create('rutas', function (Blueprint $table) {
             $table->id('id_ruta');
            $table->unsignedBigInteger('id_estado_ruta');
            $table->unsignedBigInteger('id_zona');
            $table->string('nombre_ruta', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('coordenada_inicio_lat', 10, 8);
            $table->decimal('coordenada_inicio_lng', 11, 8);
            $table->decimal('coordenada_fin_lat', 10, 8);
            $table->decimal('coordenada_fin_lng', 11, 8);
            $table->decimal('distancia_km', 8, 2);
            $table->time('horario_inicio');
            $table->time('horario_fin');
            $table->timestamps();
            
            $table->foreign('id_estado_ruta')->references('id_estado_ruta')->on('estados_ruta');
            $table->foreign('id_zona')->references('id_zona')->on('zonas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
