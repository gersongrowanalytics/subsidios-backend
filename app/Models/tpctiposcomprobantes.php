<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tpctiposcomprobantes extends Model
{
    protected $table = 'tpctiposcomprobantes';
    protected $primaryKey = 'tpcid';

    protected $fillable = [
        'tpccodigo',
        'tpcnombre',
    ];
}
