<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tputiposusuarios extends Model
{
    protected $table = 'tputiposusuarios';
    protected $primaryKey = 'tpuid';

    protected $fillable = [
        'tpunombre',
        'tpuprivilegio',
    ];
}
