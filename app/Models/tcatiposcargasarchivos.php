<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tcatiposcargasarchivos extends Model
{
    protected $table = 'tcatiposcargasarchivos';
    protected $primaryKey = 'tcaid';

    protected $fillable = [
        "usuid",
        "tcanombre",
        "tcaresponsable",
        "tcabasedatos",
        "tcaarea",
        "tcafechacargaprogramada",
    ];
}
