<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class carcargasarchivos extends Model
{
    protected $table = 'carcargasarchivos';
    protected $primaryKey = 'carid';

    protected $fillable = [
        "tcaid",
        "usuid",
        "carnombre",
        "carextension",
        "carurl",
        "carexito",
    ];
}
