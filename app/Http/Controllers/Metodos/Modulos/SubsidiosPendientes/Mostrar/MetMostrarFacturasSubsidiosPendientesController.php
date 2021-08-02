<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fdsfacturassidetalles;

class MetMostrarFacturasSubsidiosPendientesController extends Controller
{
    public function MetMostrarFacturasSubsidiosPendientes(Request $request)
    {
        $coddestinatario = $request['sdecodigodestinatario'];


        $fsis = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                ->join('proproductos as pro', 'pro.proid', 'fdsfacturassidetalles.proid')
                                ->where('fsi.fsidestinatario', $coddestinatario)
                                ->where('fdssaldo', '>', 0)
                                ->get([
                                    'fdsfacturassidetalles.fdsid',
                                    'fdsfacturassidetalles.fsiid',
                                    'fecfecha',
                                    'fsifactura',
                                    'fdsmaterial',
                                    'pro.proid',
                                    'prosku',
                                    'pronombre',
                                    'fdsvalorneto',
                                    'fdssaldo'
                                ]);

        $requestsalida = response()->json([
            "datos" => $fsis
        ]);

        return $requestsalida;


    }
}
