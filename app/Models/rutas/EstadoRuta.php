<?php

namespace App\Models\rutas;

use Illuminate\Database\Eloquent\Model;

class EstadoRuta extends Model
{
    protected $table = 'estados_ruta';
    protected $primaryKey = 'id_estado_ruta';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    // Relaciones
    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'id_estado_ruta');
    }
}
