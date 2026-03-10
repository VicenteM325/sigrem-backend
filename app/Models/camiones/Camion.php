<?php

namespace App\Models\camiones;

use Illuminate\Database\Eloquent\Model;
use App\Models\Conductor;
use App\Models\rutas\AsignacionRutaCamion;

class Camion extends Model
{
    protected $table = 'camiones';
    protected $primaryKey = 'id_camion';

    protected $fillable = [
        'id_conductor',
        'placa',
        'capacidad_toneladas',
        'estado_vehiculo'
    ];

    protected $casts = [
        'capacidad_toneladas' => 'float',
        'estado_vehiculo' => 'string'
    ];

    // Relaciones
    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'id_conductor');
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionRutaCamion::class, 'id_camion');
    }

    public function asignacionesActivas()
    {
        return $this->hasMany(AsignacionRutaCamion::class, 'id_camion')
                    ->whereIn('estado', ['programada', 'en_proceso']);
    }
}