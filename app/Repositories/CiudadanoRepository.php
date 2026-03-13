<?php

namespace App\Repositories;

use App\Models\Ciudadano;

class CiudadanoRepository
{
    public function create(int $userId, array $data = []): Ciudadano
    {
        $defaultData = [
            'puntos_acumulados' => 0,
            'nivel' => 1,
            'logros' => null,
            'preferencias' => null
        ];

        return Ciudadano::create(array_merge(
            ['id_usuario' => $userId],
            $defaultData,
            $data
        ));
    }

    public function update(Ciudadano $ciudadano, array $data): Ciudadano
    {
        $ciudadano->update($data);
        return $ciudadano->fresh();
    }

    public function delete(Ciudadano $ciudadano): bool
    {
        return $ciudadano->delete();
    }

    public function findByUserId(int $userId): ?Ciudadano
    {
        return Ciudadano::where('id_usuario', $userId)->first();
    }
    public function find(int $id): ?Ciudadano
    {
        return Ciudadano::find($id);
    }
}
