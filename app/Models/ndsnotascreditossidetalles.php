<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ndsnotascreditossidetalles extends Model
{
    protected $table = 'ndsnotascreditossidetalles';
    protected $primaryKey = 'ndsid';

    protected $fillable = [
        "fecid",
        "nsiid",
        "fsiid",
        "proid",
        "cliid",
        "ndsmaterial",
        "ndsclase",
        "ndsnotacredito",
        "ndsvalorneto",
        "ndsvalornetodolares",
        "ndspedido",
        "ndspedidooriginal",
    ];
}
