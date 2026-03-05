<?php

namespace App\Repositories;

use App\Models\rutas\EstadoRuta;
use Illuminate\Database\Eloquent\Collection;

class EstadoRutaRepository extends BaseRepository
{
    public function __construct(EstadoRuta $model)
    {
        parent::__construct($model);
    }

    public function getActivo(): ?EstadoRuta
    {
        return EstadoRuta::where('nombre', 'Activa')->first();
    }

    public function findWithRutas(int $id): ?EstadoRuta
    {
        return EstadoRuta::with('rutas')->find($id);
    }
}