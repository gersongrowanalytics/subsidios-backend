<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class coscodigossectores extends Model
{
    protected $table = 'coscodigossectores';
    protected $primaryKey = 'cosid';

    protected $fillable = [
        "coscodigo",
        "cosnombre",
    ];
}
