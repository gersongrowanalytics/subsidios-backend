<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class nsinotascreditossi extends Model
{
    protected $table = 'nsinotascreditossi';
    protected $primaryKey = 'nsiid';

    protected $fillable = [
        "fecid",
        "tpcid",
        "secid",
        "nsimoneda",
        "nsiclase",
        "nsifecha",
        "nsisap",
        "nsinotacredito",
        "nsivalorneto",
        "nsivalornetodolares",
    ];
}
