<?php

namespace App\Http\Controllers\Metodos\Modulos\NotasCredito;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;

class MetMostrarDataController extends Controller
{
    public function MetMostrarDataDistribuidorasNc(Request $request)
    {

        $sdess = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->distinct('cli.clicodigoshipto')
                                    ->get([
                                        'cli.clicodigoshipto',
                                        'cli.clisuchml'
                                    ]);
        
        $nuevaData = array();

        foreach ($sdess as $key => $sde) {
            $nuevaData[] = array(
                "clicodigoshipto" => $sde->clicodigoshipto,
                "clisuchml" => $sde->clisuchml,
                "seleccionado" => false
            );
        }
                                
        $requestsalida = response()->json([
            "respuesta" => true,
            "data" => $sdess
        ]);

        return $requestsalida;

    }
}
