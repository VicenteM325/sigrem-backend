<?php

namespace App\Models\contenedores;

use Illuminate\Database\Eloquent\Model;

class TipoMaterial extends Model
{
    protected $table = 'tipos_material';
    protected $primaryKey = 'id_material';

    protected $fillable = [
        'nombre_material',
        'descripcion'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function contenedores()
    {
        return $this->hasMany(Contenedor::class, 'id_material');
    }
}
