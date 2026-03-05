<?php

namespace App\Models\residuos;

use Illuminate\Database\Eloquent\Model;

class TipoResiduo extends Model
{
    protected $table = 'tipos_residuo';
    protected $primaryKey = 'id_tipo_residuo';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    // Relaciones
    public function rutas()
    {
        return $this->belongsToMany(Ruta::class, 'ruta_tipo_residuo', 'id_tipo_residuo', 'id_ruta');
    }
}
