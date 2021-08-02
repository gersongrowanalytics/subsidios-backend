<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class estestados extends Model
{
    protected $table = 'estestados';
    protected $primaryKey = 'estid';

    protected $fillable = ['estnombre', 'estdescripcion'];

}
