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
        // Modificando para que sesa nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono')->nullable()->after('email');
        });

        \DB::table('users')->update([
            'telefono' => '0000000000',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono')->nullable(false)->change();
        });

        \DB::table('users')->whereNull('direccion')->update([
            'direccion' => 'Dirección pendiente de actualizar'
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('direccion')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir direccion a nullable
            $table->string('direccion')->nullable()->change();
            
            $table->dropColumn('telefono');
            });
    }
};
