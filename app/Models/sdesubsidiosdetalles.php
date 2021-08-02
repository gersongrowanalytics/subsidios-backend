<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sdesubsidiosdetalles extends Model
{
    protected $table = 'sdesubsidiosdetalles';
    protected $primaryKey = 'sdeid';

    protected $fillable = [
        // "subid",
        "fecid",
        "proid",
        "cliid",
        "sdecodigosolicitante",
        "sdecodigodestinatario",
        "sdesectoruno",
        "sdesegmentoscliente",
        "sdesubsegmentoscliente",
        "sderucsubcliente",
        "sdesubcliente",
        "sdenombrecomercial",
        "sdesector",
        "sdecodigounitario",
        "sdedescripcion",
        "sdepcsapfinal",
        "sdedscto",
        "sdepcsubsidiado",
        "sdemup",
        "sdepvpigv",
        "sdedsctodos",
        "sdedestrucsap",
        "sdeinicio",
        "sdebultosacordados",
        "sdecantidadbultos",
        "sdemontoareconocer",
        "sdecantidadbultosreal",
        "sdemontoareconocerreal",
        "sdestatus",
        "sdediferenciaahorro",
        "sdeaprobado",
        "sdesac",
        "sdeencontrofactura",
        "sdependiente"
    ];
}
