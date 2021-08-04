<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cliclientes extends Model
{
    protected $table = 'cliclientes';
    protected $primaryKey = 'cliid';

    protected $fillable = [
        'clinombre',
        'clicodigo',
        'clicodigoshipto',
        'clishipto',
        'clihml',
        'clisuchml',
        'clidepartamento',
        'cligrupohml',
        'clitv',
        'clizona',
        'cliregion',
        'clicanal',
        'clitipoatencion',
        'clicanalatencion',
        'clisegmentoclientefinal',
        'clisubsegmento',
        'clisegmentoregional',
        'cligerenteregional',
        'cligerentezona',
        'cliejecutivo',
        'cliidentificadoraplicativo',
        'cliclientesac'
    ];
}