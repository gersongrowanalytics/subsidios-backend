<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class secseriescomprobantes extends Model
{
    protected $table = 'secseriescomprobantes';
    protected $primaryKey = 'secid';

    protected $fillable = [
        'tpcid',
        'secserie',
        'secdescripcion',
    ];
}
