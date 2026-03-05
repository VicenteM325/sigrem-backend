<?php

namespace App\Models\rutas;

use Illuminate\Database\Eloquent\Model;
use App\Models\recoleccion\DiaRecoleccion;
use App\Models\residuos\TipoResiduo;
use App\Models\espacios\Zona;

class Ruta extends Model
{
    protected $table = 'rutas';
    protected $primaryKey = 'id_ruta';

    protected $fillable = [
        'id_estado_ruta',
        'id_zona',
        'nombre_ruta',
        'descripcion',
        'coordenada_inicio_lat',
        'coordenada_inicio_lng',
        'coordenada_fin_lat',
        'coordenada_fin_lng',
        'distancia_km',
        'horario_inicio',
        'horario_fin'
    ];

    protected $casts = [
        'coordenada_inicio_lat' => 'float',
        'coordenada_inicio_lng' => 'float',
        'coordenada_fin_lat' => 'float',
        'coordenada_fin_lng' => 'float',
        'distancia_km' => 'float',
        'horario_inicio' => 'datetime:H:i',
        'horario_fin' => 'datetime:H:i'
    ];

    // Relaciones
    public function estado()
    {
        return $this->belongsTo(EstadoRuta::class, 'id_estado_ruta');
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'id_zona');
    }

    public function puntosRuta()
    {
        return $this->hasMany(PuntoRuta::class, 'id_ruta')->orderBy('orden');
    }

    public function tiposResiduo()
    {
        return $this->belongsToMany(TipoResiduo::class, 'ruta_tipo_residuo', 'id_ruta', 'id_tipo_residuo');
    }

    public function diasRecoleccion()
    {
        return $this->hasMany(DiaRecoleccion::class, 'id_ruta');
    }

   /* public function asignaciones()
    {
        return $this->hasMany(AsignacionRutaCamion::class, 'id_ruta');
    }*/

    // Accessors
    public function getHorarioAttribute(): string
    {
        return $this->horario_inicio->format('H:i') . ' - ' . $this->horario_fin->format('H:i');
    }

    public function getDiasTextoAttribute(): string
    {
        return $this->diasRecoleccion->pluck('nombre_dia')->join(', ');
    }

    public function getGeojsonAttribute(): array
    {
        $coordenadas = [
            [$this->coordenada_inicio_lng, $this->coordenada_inicio_lat]
        ];

        foreach ($this->puntosRuta as $punto) {
            $coordenadas[] = [$punto->longitud, $punto->latitud];
        }

        $coordenadas[] = [$this->coordenada_fin_lng, $this->coordenada_fin_lat];

        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $coordenadas
            ],
            'properties' => [
                'id' => $this->id_ruta,
                'nombre' => $this->nombre_ruta,
                'distancia' => $this->distancia_km,
                'horario' => $this->horario,
                'dias' => $this->diasTexto,
                'color' => $this->getColorByEstado()
            ]
        ];
    }

    private function getColorByEstado(): string
    {
        return match($this->estado?->nombre) {
            'Activa' => '#10b981', // verde
            'En mantenimiento' => '#f59e0b', // amarillo
            'Inactiva' => '#ef4444', // rojo
            default => '#3b82f6' // azul
        };
    }
}
