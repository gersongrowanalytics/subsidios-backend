<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fecfechas extends Model
{
    protected $table = 'fecfechas';
    protected $primaryKey = 'fecid';

    protected $fillable = [
        "fecid",
        "fecfecha",
        "fecmesabreviacion",
        "fecdianumero",
        "fecmesnumero",
        "fecanionumero",
        "fecdiatexto",
        "fecmestexto",
        "fecaniotexto",
        "fecmesabierto"
    ];
}
