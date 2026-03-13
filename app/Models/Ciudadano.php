<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudadano extends Model
{
    protected $primaryKey = 'id_ciudadano';

    protected $fillable = [
       'id_usuario',
        'puntos_acumulados',
        'nivel',
        'logros',
        'preferencias',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'id_usuario');
    }
    public function entregas()
    {
        return $this->hasMany(EntregaReciclaje::class, 'id_ciudadano');
    }
}
