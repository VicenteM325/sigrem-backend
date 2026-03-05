<?php

namespace App\Models\rutas;

use Illuminate\Database\Eloquent\Model;

class PuntoRuta extends Model
{
    protected $table = 'puntos_ruta';
    protected $primaryKey = 'id_punto_ruta';

    protected $fillable = [
        'id_ruta',
        'latitud',
        'longitud',
        'orden'
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
        'orden' => 'integer'
    ];

    // Relaciones
    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }
}
