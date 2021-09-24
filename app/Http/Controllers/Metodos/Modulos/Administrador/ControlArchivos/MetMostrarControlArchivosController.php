<?php

namespace App\Http\Controllers\Metodos\Modulos\Administrador\ControlArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\carcargasarchivos;

class MetMostrarControlArchivosController extends Controller
{
    public function MetMostrarControlArchivos(Request $request)
    {

        $respuesta = true;

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        if($fechaInicio != null){
            $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        }else{

        }
        
        $cars = carcargasarchivos::join('tcatiposcargasarchivos as tca', 'tca.tcaid', 'carcargasarchivos.tcaid')
                                    ->join('usuusuarios as usu', 'usu.usuid', 'carcargasarchivos.usuid')
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        if($fechaInicio == null){

                                        }else{
                                            // $query->whereBetween('carcargasarchivos.created_at', [$fechaInicio, $fechaFinal]);
                                        }
                                    })
                                    ->orderBy('carcargasarchivos.created_at')
                                    ->get([
                                        'carcargasarchivos.carid',
                                        'tca.tcaid',
                                        'tca.tcanombre',
                                        'carnombre',
                                        'carextension',
                                        'usuusuario',
                                        'carurl',
                                        'carcargasarchivos.created_at'
                                    ]);

        if(sizeof($cars) > 0){
            $respuesta = true;
        }else{
            $respuesta = false;
        }





        $requestsalida = response()->json([
            "datos" => $cars,
            "respuesta" => $respuesta,
        ]);

        return $requestsalida;        

    }
}
