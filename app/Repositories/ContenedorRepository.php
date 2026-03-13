<?php

namespace App\Repositories;

use App\Models\contenedores\Contenedor;
use Illuminate\Database\Eloquent\Collection;

class ContenedorRepository extends BaseRepository
{
    public function __construct(Contenedor $model)
    {
        parent::__construct($model);
    }

    public function findWithRelations(int $id): ?Contenedor
    {
        return Contenedor::with(['puntoVerde', 'material', 'vaciados'])->find($id);
    }

    public function getAllWithRelations(): Collection
    {
        return Contenedor::with(['puntoVerde', 'material'])->get();
    }

    public function getByPuntoVerde(int $idPuntoVerde): Collection
    {
        return Contenedor::with(['material'])
            ->where('id_punto_verde', $idPuntoVerde)
            ->get();
    }

    public function getPorLlenar(float $porcentajeMinimo = 80): Collection
    {
        return Contenedor::with(['puntoVerde', 'material'])
            ->where('porcentaje_llenado', '>=', $porcentajeMinimo)
            ->where('estado_contenedor', 'disponible')
            ->get();
    }

    public function getEnMantenimiento(): Collection
    {
        return Contenedor::with(['puntoVerde', 'material'])
            ->where('estado_contenedor', 'mantenimiento')
            ->get();
    }

    public function actualizarLlenado(int $id, float $nuevoPorcentaje): bool
    {
        $contenedor = $this->find($id);
        if (!$contenedor) return false;

        $contenedor->porcentaje_llenado = $nuevoPorcentaje;

        if ($nuevoPorcentaje >= 90) {
            $contenedor->estado_contenedor = 'lleno';
        } elseif ($contenedor->estado_contenedor === 'lleno' && $nuevoPorcentaje < 90) {
            $contenedor->estado_contenedor = 'disponible';
        }

        return $contenedor->save();
    }
}
