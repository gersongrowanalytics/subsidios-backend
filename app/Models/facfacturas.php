<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class facfacturas extends Model
{
    protected $table = 'facfacturas';
    protected $primaryKey = 'facid';
    
    protected $fillable = [
        'fecid',
        'cliid',
        'tpcid',
        'secid',
        'faccodigocompleto',
        'facserie',
        'faccorrelativo',
        'faccodigo',
        'facsap',
        'facsubtotal',
        'facimpuesto',
        'factotal',
    ];
}
