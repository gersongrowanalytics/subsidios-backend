<?php

namespace App\Http\Controllers\Metodos\Modulos\Home\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\tprtipospromociones;
use App\Models\areareasestados;
use App\Models\espestadospendientes;
use \DateTime;

class MetMostrarEstadosPendientesController extends Controller
{
    public function MetMostrarEstadosPendientes(Request $request)
    {

        $data = [];
        $espsDistribuidoras = [];

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        // if($fechaInicio != null){
        //     $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            
        // }
        
        if($fechaFinal != null){
            $fechaFinal = date("Y-m", strtotime($fechaFinal));
        }

        $tprs = tprtipospromociones::get();

        foreach($tprs as $posicionTpr => $tpr){

            $ares = espestadospendientes::join('areareasestados as are', 'are.areid', 'espestadospendientes.areid')
                                        ->join('tprtipospromociones as tpr', 'tpr.tprid', 'are.tprid')
                                        ->join('fecfechas as fec', 'fec.fecid', 'espestadospendientes.fecid')
                                        ->where('tpr.tprid', $tpr->tprid)
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            // if($fechaInicio != null){
                                                // $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                                $query->where('fecfecha', $fechaFinal."-01");
                                            // }
                                        })
                                        ->distinct('are.areid')
                                        ->get([
                                            'are.areid',
                                            'areicono',
                                            'arenombre',
                                            'areporcentaje',
                                        ]);

            $aresn = array(
                array(),
                array(),
                array(),
                array(),
            );

            $fechaActual = date('Y-m-d');
            $date1 = new DateTime($fechaActual);

            foreach($ares as $posicionAre => $are){

                $esps = espestadospendientes::join('perpersonas as per', 'per.perid', 'espestadospendientes.perid')
                                            ->where('areid', $are->areid)
                                            ->get([
                                                'espfechaprogramado',
                                                'espchacargareal',
                                                'espfechactualizacion',
                                                'espbasedato',
                                                'espresponsable',
                                                'espdiaretraso',
                                                'pernombrecompleto',
                                                'pernombre',
                                                'perapellidopaterno',
                                                'perapellidomaterno',
                                            ]);

                foreach($esps as $posicionEsp => $esp){
                    
                    $diaRetraso = $esp->espdiaretraso;

                    if($esp->espfechactualizacion == null){

                        $fecha_carga_real = date("Y-m-d", strtotime($esp->espfechaprogramado));
                        
                        $date2 = new DateTime($fecha_carga_real);

                        if($date1 > $date2){
                            $diff = $date1->diff($date2);

                            if($diff->days > 0){
                                $diaRetraso = $diff->days;
                            }else{
                                $diaRetraso = "0";
                            }

                        }else{
                            $diaRetraso = "0";
                        }
                    }

                    $esps[$posicionEsp]['espdiaretraso'] = $diaRetraso;

                }

                $ares[$posicionAre]['esps'] = $esps;

                if($are->arenombre == "SAC Sell Out Detalle"){
                    $aresn[3] = $ares[$posicionAre];
                }else if($are->arenombre == "SAC Sell Out"){
                    $aresn[2] = $ares[$posicionAre];
                }else if($are->arenombre == "SAC Sell In"){
                    $aresn[1] = $ares[$posicionAre];
                }else{
                    $aresn[0] = $ares[$posicionAre];
                }
            }

            // $tprs[$posicionTpr]['ares'] = $ares;
            $tprs[$posicionTpr]['ares'] = $aresn;

            if($posicionTpr == 0){
                $tprs[$posicionTpr]['seleccionado'] = true; 
            }


            $espsDistribuidoras = espestadospendientes::join('perpersonas as per', 'per.perid', 'espestadospendientes.perid')
                                                    ->join('areareasestados as are', 'are.areid', 'espestadospendientes.areid')
                                                    ->join('tprtipospromociones as tpr', 'tpr.tprid', 'are.tprid')
                                                    ->join('fecfechas as fec', 'fec.fecid', 'espestadospendientes.fecid')
                                                    ->leftjoin('cliclientes as cli', 'cli.cliid', 'espestadospendientes.cliid')
                                                    ->leftjoin('zonzonas as zon', 'zon.zonid', 'cli.zonid')
                                                    ->where('are.arenombre', 'SAC Sell Out Detalle')
                                                    ->where('tpr.tprid', $tpr->tprid)
                                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                                        $query->where('fecfecha', $fechaFinal."-01");
                                                    })
                                                    ->orderby('zonorden', 'desc')
                                                    ->get([
                                                        'espfechaprogramado',
                                                        'espchacargareal',
                                                        'espfechactualizacion',
                                                        'espbasedato',
                                                        'espresponsable',
                                                        'espdiaretraso',
                                                        'pernombrecompleto',
                                                        'pernombre',
                                                        'perapellidopaterno',
                                                        'perapellidomaterno',
                                                        'zon.zonnombre',
                                                        'clihml',
                                                        'clisuchml',
                                                        'clitv',
                                                        'cliclientesac'
                                                    ]);

            foreach($espsDistribuidoras as $posicionEsp => $esp){
                    
                    $diaRetraso = $esp->espdiaretraso;

                    if($esp->espfechactualizacion == null){

                        $fecha_carga_real = date("Y-m-d", strtotime($esp->espfechaprogramado));
                        
                        $date2 = new DateTime($fecha_carga_real);

                        if($date1 > $date2){
                            $diff = $date1->diff($date2);

                            if($diff->days > 0){
                                $diaRetraso = $diff->days;
                            }else{
                                $diaRetraso = "0";
                            }

                        }else{
                            $diaRetraso = "0";
                        }
                    }

                    $espsDistribuidoras[$posicionEsp]['espdiaretraso'] = $diaRetraso;

            }

        }

        $data = $tprs;









        $requestsalida = response()->json([
            "datos" => $data,
            "espsDistribuidoras" => $espsDistribuidoras
        ]);

        return $requestsalida;

    }
}
