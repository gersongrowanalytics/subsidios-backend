<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class csoclientesso extends Model
{
    protected $table = 'csoclientesso';
    protected $primaryKey = 'csoid';

    protected $fillable = [
        'csocodsolicitante',
        'csonombsolicitante',
        'csocoddestinatario',
        'csonombdestinatario',
        'csorucsubcliente',
        'csosubcliente',
        'csosectorpbi',
        'csosegmento',
        'csosubsegmento',
        'csonombrecomercial',
    ];
}
