<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cbucostosbultos extends Model
{
    protected $table = 'cbucostosbultos';
    protected $primaryKey = 'cbuid';

    protected $fillable = [
        'fecid',
        'proid',
        'cbusku',
        'cbudescsku',
        'cbudirecto',
        'cbuindirecto',
        'cbutotal'
    ];
}


