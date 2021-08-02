<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class marmarcas extends Model
{
    protected $table = 'marmarcas';
    protected $primaryKey = 'marid';

    protected $fillable = [
        'marnombre',
    ];
}
