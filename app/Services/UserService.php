<?php

namespace App\Services;

use App\Models\User;
use App\Models\Conductor;
use App\Models\Ciudadano;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use DomainExeption;

class UserService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'direccion' => $data['direccion'] ?? null,
                'estado' => true,
            ]);

            // asignar rol
            $user->assignRole($data['role']);

            // crear perfil segun rol
            $this->createProfileByRole($user, $data);

            return $user;
        });
    }

     private function createProfileByRole(User $user, array $data)
    {
        match ($data['role']) {

            'conductor' => $this->createConductor($user, $data),

            'ciudadano' => $this->createCiudadano($user, $data),

            default => null,
        };
    }

    private function createConductor(User $user, array $data)
    {
        if (empty($data['licencia'])) {
            throw new DomainException(
                'Un conductor debe tener licencia'
            );
        }

        Conductor::create([
            'id_usuario' => $user->id,
            'telefono' => $data['telefono'] ?? null,
            'licencia' => $data['licencia'],
            'estado' => true,
        ]);
    }

    private function createCiudadano(User $user, array $data)
    {
        Ciudadano::create([
            'id_usuario' => $user->id,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $user->direccion,
        ]);
    }
}