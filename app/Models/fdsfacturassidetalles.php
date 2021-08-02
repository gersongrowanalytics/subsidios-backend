<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fdsfacturassidetalles extends Model
{
    protected $table = 'fdsfacturassidetalles';
    protected $primaryKey = 'fdsid';

    protected $fillable = [
        "fecid",
        "fsiid",
        "proid",
        "cliid",
        "fdsmaterial",
        "fdsmoneda",
        "fdsvalorneto",
        "fdsvalornetodolares",
        "fdspedido",
        "fdspedidooriginal",
        "fdssaldo",
        "fdsreconocer",
        "fdstreintaporciento",
        "fdsobservacion",
        "fdsnotacredito"
    ];
}
