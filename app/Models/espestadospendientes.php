<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class espestadospendientes extends Model
{
    protected $table = 'espestadospendientes';
    protected $primaryKey = 'espid';

    protected $fillable = [
        'fecid',
        'perid',
        'areid',
        'espfechaprogramado',
        'espchacargareal',
        'espfechactualizacion',
        'espbasedato',
        'espresponsable',
        'espdiaretraso',
    ];
}
