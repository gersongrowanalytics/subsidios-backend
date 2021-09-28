<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class perpersonas extends Model
{
    protected $table = 'perpersonas';
    protected $primaryKey = 'perid';

    protected $fillable = [
        "pernumerodocumentoidentidad",
        "pernombrecompleto",
        "pernombre",
        "perapellidopaterno",
        "perapellidomaterno",
        "percumpleanios",
        "pernumero",
        "perid",
    ];
}
