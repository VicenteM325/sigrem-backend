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
        //Actualizar atributos para perfil ciudadano eliminadndo  atributos repetidos con user
        Schema::table('ciudadanos', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'direccion']);
        });

        Schema::table('ciudadanos', function (Blueprint $table) {
            $table->integer('puntos_acumulados')->default(0)->after('id_usuario');
            $table->integer('nivel')->default(1)->after('puntos_acumulados');
            $table->json('logros')->nullable()->after('nivel');
            $table->json('preferencias')->nullable()->after('logros');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('ciudadanos', function (Blueprint $table) {
            $table->dropColumn([
                'puntos_acumulados',
                'nivel',
                'logros',
                'preferencias',
            ]);
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
        });
    }
};
