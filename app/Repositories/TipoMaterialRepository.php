<?php

namespace App\Repositories;

use App\Models\contenedores\TipoMaterial;
use Illuminate\Database\Eloquent\Collection;

class TipoMaterialRepository extends BaseRepository
{
    public function __construct(TipoMaterial $model)
    {
        parent::__construct($model);
    }

    public function getForSelect(): Collection
    {
        return $this->model->orderBy('nombre_material')->get();
    }

    public function findByName(string $name): ?TipoMaterial
    {
        return $this->model->where('nombre_material', $name)->first();
    }
}
