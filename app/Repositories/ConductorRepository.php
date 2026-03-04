<?php

namespace App\Repositories;

use App\Models\Conductor;

class ConductorRepository
{
    public function create(int $userId, array $data): Conductor
    {
        return Conductor::create(array_merge(
            ['id_usuario' => $userId],
            $data
        ));
    }

    public function update(Conductor $conductor, array $data): Conductor
    {
        $conductor->update($data);
        return $conductor->fresh();
    }

    public function delete(Conductor $conductor): bool
    {
        return $conductor->delete();
    }

    public function findByUserId(int $userId): ?Conductor
    {
        return Conductor::where('id_usuario', $userId)->first();
    }
}