<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tictipocambios extends Model
{
    protected $table = 'tictipocambios';
    protected $primaryKey = 'ticid';

    protected $fillable = [
        'fecid',
        'ticfecha',
        'tictc'
    ];
}
