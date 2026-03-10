<?php

namespace App\Repositories;

use App\Models\residuos\TipoResiduo;
use Illuminate\Database\Eloquent\Collection;

class TipoResiduoRepository extends BaseRepository
{
    public function __construct(TipoResiduo $model)
    {
        parent::__construct($model);
    }

    public function getAllWithRutas(): Collection
    {
        return TipoResiduo::with('rutas')->get();
    }
}