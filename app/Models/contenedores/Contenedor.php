<?php

namespace App\Models\contenedores;

use Illuminate\Database\Eloquent\Model;
use App\Models\espacios\PuntoVerde;

class Contenedor extends Model
{
    protected $table = 'contenedores';
    protected $primaryKey = 'id_contenedor';

    protected $fillable = [
        'id_punto_verde',
        'id_material',
        'capacidad_m3',
        'porcentaje_llenado',
        'estado_contenedor'
    ];

    protected $casts = [
        'capacidad_m3' => 'float',
        'porcentaje_llenado' => 'float'
    ];

    public function puntoVerde()
    {
        return $this->belongsTo(PuntoVerde::class, 'id_punto_verde');
    }

    public function material()
    {
        return $this->belongsTo(TipoMaterial::class, 'id_material');
    }

    public function vaciados()
    {
        return $this->hasMany(VaciadoContenedor::class, 'id_contenedor');
    }

    public function vaciadosPendientes()
    {
        return $this->hasMany(VaciadoContenedor::class, 'id_contenedor')
                    ->where('estado', 'programado')
                    ->where('fecha_programada', '>=', now());
    }
}
