<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    protected $primaryKey = 'id_conductor';

    protected $fillable = [
        'id_usuario',
        'licencia',
        'fecha_vencimiento_licencia', 
        'categoria_licencia',            
        'disponible', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'id_usuario');
    }
}
