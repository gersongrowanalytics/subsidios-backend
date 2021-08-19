<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class areareasestados extends Model
{
    protected $table = 'areareasestados';
    protected $primaryKey = 'areid';

    protected $fillable = [
        'areid', 
        'tprid', 
        'areicono',
        'arenombre',
        'areporcentaje',
    ];
}
