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
        Schema::create('evidencias_denuncia', function (Blueprint $table) {
            $table->id('id_evidencia');
            $table->unsignedBigInteger('id_denuncia');
            $table->unsignedBigInteger('id_cuadrilla')->nullable();
            $table->enum('tipo_foto', ['antes', 'despues']);
            $table->string('url_imagen', 500);
            $table->dateTime('fecha');
            $table->timestamps();
            
            $table->foreign('id_denuncia')->references('id_denuncia')->on('denuncias')->onDelete('cascade');
            $table->foreign('id_cuadrilla')->references('id_cuadrilla')->on('cuadrillas_limpieza')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidencias_denuncia');
    }
};
