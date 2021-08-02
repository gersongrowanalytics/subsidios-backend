<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tpptipospermisos extends Model
{
    protected $table = 'tpptipospermisos';
    protected $primaryKey = 'tppid';

    protected $fillable = [
        'tppnombre',
        'tppdescripcion',
    ];
}
