<?php

namespace App\Models\contenedores;

use Illuminate\Database\Eloquent\Model;

class VaciadoContenedor extends Model
{
    protected $table = 'vaciados_contenedor';
    protected $primaryKey = 'id_vaciado';

    protected $fillable = [
        'id_contenedor',
        'fecha_programada',
        'fecha_real',
        'estado'
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_real' => 'datetime'
    ];

    public function contenedor()
    {
        return $this->belongsTo(Contenedor::class, 'id_contenedor');
    }
}
