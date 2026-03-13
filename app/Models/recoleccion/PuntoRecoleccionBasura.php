<?php

namespace App\Models\recoleccion;

use Illuminate\Database\Eloquent\Model;

class PuntoRecoleccionBasura extends Model
{
    protected $table = 'puntos_recoleccion_basura';
    protected $primaryKey = 'id_punto_basura';

    protected $fillable = [
        'id_recoleccion',
        'latitud',
        'longitud',
        'volumen_estimado_kg',
        'estado_recoleccion'
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'volumen_estimado_kg' => 'decimal:2'
    ];

    public function recoleccion()
    {
        return $this->belongsTo(Recoleccion::class, 'id_recoleccion');
    }
}
