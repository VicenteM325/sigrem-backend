<?php

namespace App\Models\espacios;

use Illuminate\Database\Eloquent\Model;
use App\Models\rutas\Ruta;

class Zona extends Model
{
    protected $table = 'zonas';
    protected $primaryKey = 'id_zona';

    protected $fillable = [
        'nombre_zona',
        'densidad_poblacional',
        'coordenadas_poligono'
    ];

    protected $casts = [
        'densidad_poblacional' => 'float',
        'coordenadas_poligono' => 'array'
    ];

    // Relaciones
    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'id_zona');
    }

    public function tiposZona()
    {
        return $this->hasMany(TipoZona::class, 'id_zona');
    }
/*
    public function puntosVerdes()
    {
        return $this->hasMany(PuntoVerde::class, 'id_zona');
    }

    public function denuncias()
    {
        return $this->hasMany(Denuncia::class, 'id_zona');
    }*/
}
