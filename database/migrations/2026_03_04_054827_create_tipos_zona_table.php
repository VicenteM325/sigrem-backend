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
        Schema::create('tipos_zona', function (Blueprint $table) {
            $table->id('id_tipo_zona');
            $table->unsignedBigInteger('id_zona');
            $table->string('nombre_tipo_zona', 50);
            $table->timestamps();

            $table->foreign('id_zona')->references('id_zona')->on('zonas')->onDelete('cascade');
            $table->unique(['id_zona', 'nombre_tipo_zona']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_zona');
    }
};
