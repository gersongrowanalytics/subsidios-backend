<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ncdnotascreditosdetalles extends Model
{
    protected $table = 'ncdnotascreditosdetalles';
    protected $primaryKey = 'ncdid';

    protected $fillable = [
        "fecid",
        "ntcid",
        "facid",
        "cliid",
        "proid",
        "sdeid",
        "ncdcantidad",
        "ncdtotal"
    ];
}
