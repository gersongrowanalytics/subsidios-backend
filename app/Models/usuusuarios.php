<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class usuusuarios extends Model
{
    protected $table = 'usuusuarios';
    protected $primaryKey = 'usuid';

    protected $fillable = [
        "tpuid",
        "perid",
        "estid",
        "usucodigo",
        "usuusuario",
        "usucorreo",
        "usucontrasenia",
        "usutoken",
        "usuimagen",
        "usuid"
    ];
}
