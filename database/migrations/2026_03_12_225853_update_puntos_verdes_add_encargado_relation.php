<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('puntos_verdes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_encargado')->nullable()->after('horario_atencion');
            $table->foreign('id_encargado')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('puntos_verdes', function (Blueprint $table) {
            $table->dropColumn('encargado');
        });
    }

    public function down(): void
    {
        Schema::table('puntos_verdes', function (Blueprint $table) {
            $table->string('encargado', 100)->nullable();
        });
        Schema::table('puntos_verdes', function (Blueprint $table) {
            $table->dropForeign(['id_encargado']);
            $table->dropColumn('id_encargado');
        });
    }
};
