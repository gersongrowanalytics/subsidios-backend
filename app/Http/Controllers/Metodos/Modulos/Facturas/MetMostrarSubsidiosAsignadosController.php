<?php

namespace App\Http\Controllers\Metodos\Modulos\Facturas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sfssubsidiosfacturassi;

class MetMostrarSubsidiosAsignadosController extends Controller
{
    public function MetMostrarSubsidiosAsignados(Request $request)
    {

        $fdsid = $request['fdsid'];

        $sfss = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                    ->join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sde.fecid')
                                    ->join('proproductos as pro', 'pro.proid', 'sde.proid')
                                    ->where('fdsid', $fdsid)
                                    ->get([
                                        'sfsvalorizado',
                                        'sfssaldoanterior',
                                        'sfssaldonuevo',
                                        'sdecodigosolicitante',
                                        'sdecodigodestinatario',
                                        'sderucsubcliente',
                                        'sdecantidadbultosreal',
                                        'sdemontoareconocerreal',
                                        'fecfecha',
                                        // 'fsifecha as fecfecha',
                                        'fecanionumero',
                                        'fecmesabreviacion',
                                        'pro.proid',
                                        'prosku',
                                        'pronombre',
                                        'sdedestrucsap'
                                    ]);

        $sumSfss = sfssubsidiosfacturassi::where('fdsid', $fdsid)
                                    ->sum('sfsvalorizado');

        $requestsalida = response()->json([
            "datos" => $sfss,
            "total" => $sumSfss
        ]);

        return $requestsalida;

    }
}
