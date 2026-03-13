<?php

namespace App\Models\recoleccion;

use Illuminate\Database\Eloquent\Model;
use App\Models\rutas\AsignacionRutaCamion;

class Recoleccion extends Model
{
    protected $table = 'recolecciones';
    protected $primaryKey = 'id_recoleccion';

    protected $fillable = [
        'id_asignacion',
        'hora_inicio',
        'hora_fin',
        'basura_recolectada_ton',
        'estado_recoleccion',
        'observaciones'
    ];

    protected $casts = [
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'basura_recolectada_ton' => 'decimal:2'
    ];

    // Relaciones
    public function asignacion()
    {
        return $this->belongsTo(AsignacionRutaCamion::class, 'id_asignacion');
    }

    public function puntosBasura()
    {
        return $this->hasMany(PuntoRecoleccionBasura::class, 'id_recoleccion');
    }

}
