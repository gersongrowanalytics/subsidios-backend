<?php

namespace App\Http\Controllers\Metodos\Modulos\ControlPanel\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\tcatiposcargasarchivos;

class MetMostrarControlPanelController extends Controller
{
    public function MetMostrarControlPanel()
    {
        $areas = tcatiposcargasarchivos::distinct('tcaarea')->get();

        foreach($areas as $posicionArea => $area){
            $tcas = tcatiposcargasarchivos::leftjoin('carcargasarchivos', 'car.tcaid', 'tcatiposcargasarchivos.tcaid')
                                        ->where('tcatiposcargasarchivos.tcaarea', $area->tcaarea)
                                        ->get();

            $areas[$posicionArea]['archivos'] = $tcas;
        }



        $requestsalida = response()->json([
            "datos" => $areas
        ]);

        return $requestsalida;

    }
}
