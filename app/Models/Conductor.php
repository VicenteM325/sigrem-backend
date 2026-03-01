<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    protected $primaryKey = 'id_conductor';

    protected $fillable = [
        'id_usuario',
        'telefono',
        'licencia',
        'estado'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'id_usuario');
    }
}
