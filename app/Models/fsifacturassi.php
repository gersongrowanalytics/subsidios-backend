<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fsifacturassi extends Model
{
    protected $table = 'fsifacturassi';
    protected $primaryKey = 'fsiid';

    protected $fillable = [
        "fecid",
        "cliid",
        "tpcid",
        "secid",
        "fsisolicitante",
        "fsidestinatario",
        "fsimoneda",
        "fsiclase",
        "fsifecha",
        "fsisap",
        "fsifactura",
        "fsivalorneto",
        "fsivalornetodolares",
        "fsipedido",
        "fsipedidooriginal",
    ];
}
