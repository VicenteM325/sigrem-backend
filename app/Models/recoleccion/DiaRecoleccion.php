<?php

namespace App\Models\recoleccion;

use Illuminate\Database\Eloquent\Model;

class DiaRecoleccion extends Model
{
    protected $table = 'dias_recoleccion';
    protected $primaryKey = 'id_dia';

    protected $fillable = [
        'id_ruta',
        'nombre_dia'
    ];

    // Relaciones
    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }
}
