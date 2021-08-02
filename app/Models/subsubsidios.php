<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class subsubsidios extends Model
{
    protected $table = 'subsubsidios';
    protected $primaryKey = 'subid';

    protected $fillable = [
        'cliid',
        'fecid',
        'subtotal',
    ];
}
