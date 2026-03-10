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
        Schema::create('camiones', function (Blueprint $table) {
            $table->id('id_camion');
            $table->unsignedBigInteger('id_conductor')->nullable();
            $table->string('placa', 20)->unique();
            $table->decimal('capacidad_toneladas', 8, 2);
            $table->enum('estado_vehiculo', ['operativo', 'mantenimiento', 'fuera_servicio'])->default('operativo');
            $table->timestamps();
            
            $table->foreign('id_conductor')->references('id_conductor')->on('conductors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camiones');
    }
};
