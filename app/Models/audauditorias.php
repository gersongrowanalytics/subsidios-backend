<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class audauditorias extends Model
{
    protected $table = 'audauditorias';
    protected $primaryKey = 'audid';

    protected $fillable = [
        'audip', 
        'audjsonentrada',
        'audjsonsalida',
        'auddescripcion',
        'audaccion',
        'audruta',
        'audlog',
        'audpk',
    ];
}
