<?php

namespace App\Models\rutas;

use Illuminate\Database\Eloquent\Model;
use App\Models\rutas\Ruta;
use App\Models\camiones\Camion;
use App\Models\recoleccion\Recoleccion;

class AsignacionRutaCamion extends Model
{
    protected $table = 'asignaciones_ruta_camion';
    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_ruta',
        'id_camion',
        'fecha_programada',
        'estado',
        'total_estimado_kg'
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'total_estimado_kg' => 'float',
        'estado' => 'string'
    ];

    // Relaciones
    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }

    public function camion()
    {
        return $this->belongsTo(Camion::class, 'id_camion');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->whereIn('estado', ['programada', 'en_proceso']);
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_programada', $fecha);
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_programada', [$fechaInicio, $fechaFin]);
    }
    public function recoleccion()
    {
        return $this->hasOne(Recoleccion::class, 'id_asignacion', 'id_asignacion');
    }
}
