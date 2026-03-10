<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sigrem.com'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
                'estado' => true,
                'telefono' => '40270651', 
                'direccion' => 'Oficina Central',
            ]
        );
        $admin->assignRole('super-admin');
    }
}
