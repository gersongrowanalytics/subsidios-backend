<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class zonzonas extends Model
{
    protected $table = 'zonzonas';
    protected $primaryKey = 'zonid';

    protected $fillable = [
        "zonid",
        "zonnombre"
    ];
}
