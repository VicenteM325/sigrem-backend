<?php

namespace App\Repositories;

use App\Models\espacios\Zona;
use Illuminate\Database\Eloquent\Collection;

class ZonaRepository extends BaseRepository
{
    public function __construct(Zona $model)
    {
        parent::__construct($model);
    }

    public function findWithRelations(int $id): ?Zona
    {
        return Zona::with(['rutas', 'tiposZona'])->find($id); //Sin 'puntosVerdes' por ahora
    }

    public function getAllWithRutas(): Collection
    {
        return Zona::with(['rutas' => function($q) {
            $q->with(['estado', 'diasRecoleccion']);
        }])->get();
    }

    public function getZonasActivas(): Collection
    {
        return Zona::whereHas('rutas', function($q) {
            $q->whereHas('estado', fn($q) => $q->where('nombre', 'Activa'));
        })->get();
    }

    public function findByNombre(string $nombre): ?Zona
    {
        return Zona::where('nombre_zona', 'LIKE', "%{$nombre}%")->first();
    }
}
