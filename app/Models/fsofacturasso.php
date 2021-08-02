<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fsofacturasso extends Model
{
    protected $table = 'fsofacturasso';
    protected $primaryKey = 'fsoid';

    protected $fillable = [
        "fecid",
        "cliid",
        "proid",
        "fsoruc",
        "fsocantidadbulto",
        "fsoventasinigv"
    ];
}
