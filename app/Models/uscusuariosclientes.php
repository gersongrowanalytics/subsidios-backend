<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class uscusuariosclientes extends Model
{
    protected $table = 'uscusuariosclientes';
    protected $primaryKey = 'ussid';

    protected $fillable = [
        'usuid',
        'cliid',
    ];
}
