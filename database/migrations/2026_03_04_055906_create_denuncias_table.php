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
        Schema::create('denuncias', function (Blueprint $table) {
            $table->id('id_denuncia');
            $table->unsignedBigInteger('id_zona')->nullable();
            $table->unsignedBigInteger('id_ciudadano')->nullable();
            $table->text('descripcion');
            $table->string('direccion', 255);
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->enum('tamano_basurero', ['pequeño', 'mediano', 'grande'])->default('pequeño');
            $table->dateTime('fecha_denuncia');
            $table->enum('estado', ['recibida', 'en_revision', 'asignada', 'en_atencion', 'atendida', 'cerrada'])->default('recibida');
            $table->string('foto_url')->nullable();
            $table->timestamps();
            
            $table->foreign('id_zona')->references('id_zona')->on('zonas')->onDelete('set null');
            $table->foreign('id_ciudadano')->references('id_ciudadano')->on('ciudadanos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denuncias');
    }
};
