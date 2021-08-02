<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ntcnotascreditos extends Model
{
    protected $table = 'ntcnotascreditos';
    protected $primaryKey = 'ntcid';

    protected $fillable = [
        "fecid",
        "facid",
        "cliid",
        "ntcfacturaasignada",
        "ntccodigocompleto",
        "ntcserie",
        "ntccorrelativo",
        "ntccodigo",
        "ntcsap",
        "ntcsubtotal",
        "ntcimpuesto",
        "ntctotal",
    ];
}
