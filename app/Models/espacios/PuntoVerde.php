<?php

namespace App\Models\espacios;

use Illuminate\Database\Eloquent\Model;

class PuntoVerde extends Model
{
    protected $table = 'puntos_verdes';
    protected $primaryKey = 'id_punto_verde';

    protected $fillable = [
        'id_zona',
        'nombre',
        'direccion',
        'latitud',
        'longitud',
        'capacidad_total_m3',
        'horario_atencion',
        'encargado'
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'capacidad_total_m3' => 'decimal:2'
    ];

    // Relaciones
    public function zona()
    {
        return $this->belongsTo(Zona::class, 'id_zona');
    }
}
