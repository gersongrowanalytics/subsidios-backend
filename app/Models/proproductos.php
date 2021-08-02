<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class proproductos extends Model
{
    protected $table = 'proproductos';
    protected $primaryKey = 'proid';

    protected $fillable = [
        "catid",
        "marid",
        "cosid",
        "conid",
        "pronombre",
        "prosku",
        "prosegmentacion",
        "propresentacion",
        "proconteo",
        "proformato",
        "protalla",
        "propeso",
        "promecanica",
        "profactorconversionbultos",
        "profactorconversioncajas",
        "profactorconversionpaquetes",
        "profactorconversionunidadminimaindivisible",
        "profactorconversiontoneladas",
        "profactorconversionmilesunidades",
    ];
}
