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
        Schema::table('users', function (Blueprint $table) {

            $table->string('nombres')->nullable()->after('id');
            $table->string('apellidos')->nullable()->after('nombres');

            $table->string('direccion')->nullable()->after('email');

            $table->boolean('estado')
                ->default(true)
                ->after('direccion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('users', function (Blueprint $table) {

            $table->string('name');

            $table->dropColumn([
                'nombres',
                'apellidos',
                'direccion',
                'estado'
            ]);
        });
}
};
