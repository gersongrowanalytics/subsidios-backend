<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sufsubsidiosfacturas extends Model
{
    protected $table = 'sufsubsidiosfacturas';
    protected $primaryKey = 'sufid';

    protected $fillable = [
        "sdeid",
        "facid",
        "fadid",
        "ncdid",
        "ntcid",
        "proid",
    ];
}
