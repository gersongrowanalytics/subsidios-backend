<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class concodigosnegocios extends Model
{
    protected $table = 'concodigosnegocios';
    protected $primaryKey = 'conid';

    protected $fillable = [
        "concodigo",
        "connombre",
    ];
}
