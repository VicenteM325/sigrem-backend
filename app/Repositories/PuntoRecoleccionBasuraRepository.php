<?php

namespace App\Repositories;

use App\Models\recoleccion\PuntoRecoleccionBasura;
use Illuminate\Database\Eloquent\Collection;

class PuntoRecoleccionBasuraRepository extends BaseRepository
{
    public function __construct(PuntoRecoleccionBasura $model)
    {
        parent::__construct($model);
    }

    public function findByRecoleccion(int $idRecoleccion): Collection
    {
        return PuntoRecoleccionBasura::where('id_recoleccion', $idRecoleccion)
            ->orderBy('id_punto_basura', 'asc')
            ->get();
    }
}
