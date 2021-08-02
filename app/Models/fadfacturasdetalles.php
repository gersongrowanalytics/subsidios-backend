<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fadfacturasdetalles extends Model
{
    protected $table = 'fadfacturasdetalles';
    protected $primaryKey = 'fadid';

    protected $fillable = [
        "fecid",
        "facid",
        "proid",
        "cliid",
        "fadcantidad",
        "fadpreciounitario",
        "fadsubtotal",
        "fadimpuesto",
        "fadtotal"
    ];
}
