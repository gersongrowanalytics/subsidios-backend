<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class scusubclientes extends Model
{
    protected $table = 'scusubclientes';
    protected $primaryKey = 'scuid';

    protected $fillable = [
        'cliid',
        'scunombre',
        'scucodigo',
    ];
}
