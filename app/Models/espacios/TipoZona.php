<?php

namespace App\Models\espacios;

use Illuminate\Database\Eloquent\Model;

class TipoZona extends Model
{
    protected $table = 'tipos_zona';
    protected $primaryKey = 'id_tipo_zona';

    protected $fillable = [
        'id_zona',
        'nombre_tipo_zona'
    ];

    // Relaciones
    public function zona()
    {
        return $this->belongsTo(Zona::class, 'id_zona');
    }
}
