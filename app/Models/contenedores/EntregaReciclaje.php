<?php

namespace App\Models\contenedores;

use Illuminate\Database\Eloquent\Model;
use App\Models\espacios\PuntoVerde;
use App\Models\Ciudadano;

class EntregaReciclaje extends Model
{
    protected $table = 'entregas_reciclaje';
    protected $primaryKey = 'id_entrega';

    protected $fillable = [
        'id_punto_verde',
        'id_material',
        'id_ciudadano',
        'cantidad_kg',
        'fecha_hora'
    ];

    protected $casts = [
        'cantidad_kg' => 'float',
        'fecha_hora' => 'datetime'
    ];

    public function puntoVerde()
    {
        return $this->belongsTo(PuntoVerde::class, 'id_punto_verde');
    }

    public function material()
    {
        return $this->belongsTo(TipoMaterial::class, 'id_material');
    }

    public function ciudadano()
    {
        return $this->belongsTo(Ciudadano::class, 'id_ciudadano');
    }
}
