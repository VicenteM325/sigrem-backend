<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudadano extends Model
{
    protected $primaryKey = 'id_ciudadano';

    protected $fillable = [
        'id_usuario',
        'telefono',
        'direccion'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'id_usuario');
    }
}
