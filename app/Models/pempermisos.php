<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pempermisos extends Model
{
    protected $table = 'pempermisos';
    protected $primaryKey = 'pemid';

    protected $fillable = [
        'tppid',
        "pemnombre",
        "pemslug",
        "pemruta",
    ];
}
