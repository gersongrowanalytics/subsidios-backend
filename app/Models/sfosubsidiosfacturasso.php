<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sfosubsidiosfacturasso extends Model
{
    protected $table = 'sfosubsidiosfacturasso';
    protected $primaryKey = 'sfoid';

    protected $fillable = [
        "sfoid",
        "fsoid",
        "sdeid",
        "fecid"
    ];
}
