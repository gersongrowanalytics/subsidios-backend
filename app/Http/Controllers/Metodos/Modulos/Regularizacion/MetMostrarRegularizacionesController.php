<?php

namespace App\Http\Controllers\Metodos\Modulos\Regularizacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;

class MetMostrarRegularizacionesController extends Controller
{
    public function MetMostrarRegularizaciones(Request $request)
    {

        $sdes = sdesubsidiosdetalles::join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                    ->join('coscodigossectores as cos', 'cos.cosid', 'pro.cosid')
                                    ->where('sderegularizacion', true)
                                    ->get([
                                        'sdesubsidiosdetalles.sdeid',
                                        'fecmesabreviacion',
                                        'fecanionumero',
                                        'clizona',
                                        'clitv',
                                        'clihml',
                                        'clisuchml',
                                        'sderucsubcliente',
                                        'coscodigo',
                                        'cosnombre',
                                        'prosku',
                                        'pronombre',
                                        'sdemontoareconocerreal',
                                        'sdemontoacido',
                                        'sumsfsvalorizado'
                                    ]);

        
        $requestsalida = response()->json([
            "datos" => $sdes
        ]);

        return $requestsalida;

    }
}
