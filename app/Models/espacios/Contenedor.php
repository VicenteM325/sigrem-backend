<?php

namespace App\Models\espacios;

use Illuminate\Database\Eloquent\Model;
use App\Models\catalogos\TipoMaterial;

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

    // Relaciones
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

    public function entregas()
    {
        return $this->hasMany(EntregaReciclaje::class, 'id_contenedor');
    }

    // Métodos de ayuda
    public function getCapacidadKgAttribute()
    {
        return $this->capacidad_m3 * 150;
    }

    public function getVolumenOcupadoM3Attribute()
    {
        return ($this->porcentaje_llenado / 100) * $this->capacidad_m3;
    }

    public function getKgOcupadosAttribute()
    {
        return ($this->porcentaje_llenado / 100) * $this->capacidad_kg;
    }

    public function getEstadoAlertaAttribute()
    {
        if ($this->porcentaje_llenado >= 90) return 'critico';
        if ($this->porcentaje_llenado >= 75) return 'alerta';
        if ($this->porcentaje_llenado >= 50) return 'normal';
        return 'bajo';
    }

    public function getColorEstadoAttribute()
    {
        return match($this->estado_alerta) {
            'critico' => 'red',
            'alerta' => 'yellow',
            'normal' => 'blue',
            'bajo' => 'green',
            default => 'gray'
        };
    }
}
