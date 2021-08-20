<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sfssubsidiosfacturassi extends Model
{
    protected $table = 'sfssubsidiosfacturassi';
    protected $primaryKey = 'sfsid';

    protected $fillable = [
        "sfsid",
        "fecid",
        "sdeid",
        "fsiid",
        "fdsid",
        "nsiid",
        "ndsid",
        "sfsvalorizado",
        "sfssaldoanterior",
        "sfssaldonuevo",
        "sfsobjetivo",
        "sfsdiferenciaobjetivo"
    ];
}
