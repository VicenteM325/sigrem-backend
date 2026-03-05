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
        Schema::create('zonas', function (Blueprint $table) {
            $table->id('id_zona');
            $table->string('nombre_zona', 100);
            $table->decimal('densidad_poblacional', 5, 2)->nullable()->comment('Factor de densidad 1-5');
            $table->json('coordenadas_poligono')->nullable()->comment('Polígono de la zona en GeoJSON');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zonas');
    }
};
