<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tprtipospromociones extends Model
{
    protected $table = 'tprtipospromociones';
    protected $primaryKey = 'tprid';

    protected $fillable = [
        'tprnombre'
    ];
}
