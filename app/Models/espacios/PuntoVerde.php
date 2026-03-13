<?php

namespace App\Models\espacios;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'id_encargado'
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

    public function encargado()
    {
        return $this->belongsTo(User::class, 'id_encargado');
    }
}
