<?php

namespace App\Http\Controllers\Metodos\Modulos\Home\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\tprtipospromociones;
use App\Models\areareasestados;
use App\Models\espestadospendientes;

class MetMostrarEstadosPendientesController extends Controller
{
    public function MetMostrarEstadosPendientes(Request $request)
    {

        $data = [];

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        if($fechaInicio != null){
            $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        }

        $tprs = tprtipospromociones::get();

        foreach($tprs as $posicionTpr => $tpr){

            $ares = espestadospendientes::join('areareasestados as are', 'are.areid', 'espestadospendientes.areid')
                                        ->join('tprtipospromociones as tpr', 'tpr.tprid', 'are.tprid')
                                        ->join('fecfechas as fec', 'fec.fecid', 'espestadospendientes.fecid')
                                        ->where('tpr.tprid', $tpr->tprid)
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            // if($fechaInicio != null){
                                                $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                            // }
                                        })
                                        ->distinct('are.areid')
                                        ->get([
                                            'are.areid',
                                            'areicono',
                                            'arenombre',
                                            'areporcentaje',
                                        ]);

            foreach($ares as $posicionAre => $are){

                $esps = espestadospendientes::where('areid', $are->areid)
                                            ->get([
                                                'espfechaprogramado',
                                                'espchacargareal',
                                                'espfechactualizacion',
                                                'espbasedato',
                                                'espresponsable',
                                                'espdiaretraso'
                                            ]);

                $ares[$posicionAre]['esps'] = $esps;

            }

            $tprs[$posicionTpr]['ares'] = $ares;

            if($posicionTpr == 0){
                $tprs[$posicionTpr]['seleccionado'] = true; 
            }

        }

        $data = $tprs;

        $requestsalida = response()->json([
            "datos" => $data,
        ]);

        return $requestsalida;

    }
}
