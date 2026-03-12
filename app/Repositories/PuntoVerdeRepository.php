<?php

namespace App\Repositories;

use App\Models\espacios\PuntoVerde;
use Illuminate\Database\Eloquent\Collection;

class PuntoVerdeRepository extends BaseRepository
{
    public function __construct(PuntoVerde $model)
    {
        parent::__construct($model);
    }

    public function findWithRelations(int $id): ?PuntoVerde
    {
        return PuntoVerde::with('zona')->find($id);
    }

    public function getAllWithZona(): Collection
    {
        return PuntoVerde::with('zona')
            ->orderBy('nombre')
            ->get();
    }

    public function getByZona(int $idZona): Collection
    {
        return PuntoVerde::where('id_zona', $idZona)
            ->with('zona')
            ->orderBy('nombre')
            ->get();
    }

    public function getForSelect(): array
    {
        return PuntoVerde::select('id_punto_verde as value', 'nombre as label')
            ->orderBy('nombre')
            ->get()
            ->toArray();
    }

    public function getCoordenadas(): Collection
    {
        return PuntoVerde::select('id_punto_verde', 'nombre', 'latitud', 'longitud')
            ->get();
    }
}
