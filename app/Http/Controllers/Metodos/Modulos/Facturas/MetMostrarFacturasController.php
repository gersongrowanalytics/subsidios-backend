<?php

namespace App\Http\Controllers\Metodos\Modulos\Facturas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fsifacturassi;
use App\Models\fdsfacturassidetalles;
use App\Models\sfssubsidiosfacturassi;

class MetMostrarFacturasController extends Controller
{
    public function MetMostrarFacturas(Request $request)
    {

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        // if($fechaInicio != null){
        $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
        $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        // }

        $fsis = fsifacturassi::join('fecfechas as fec', 'fec.fecid', 'fsifacturassi.fecid')
                            ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                if($fechaInicio != null){
                                    $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                }
                            })
                            // ->limit(50)
                            ->get([
                                'fsiid',
                                'fsisolicitante',
                                'fsidestinatario',
                                'fsiclase',
                                'fsifecha',
                                'fsifactura',
                                'fsivalorneto',
                                'fsipedido',
                                'fsipedidooriginal'
                            ]);

        foreach($fsis as $posicionFsi => $fsi){
            $fdss = fdsfacturassidetalles::where('fdsfacturassidetalles.fsiid', $fsi->fsiid)
                                        ->get([
                                            'fdsid',
                                            'proid',
                                            'fdsmaterial',
                                            'fdsvalorneto',
                                            'fdssaldo',
                                            'fdsreconocer',
                                            'fdstreintaporciento',
                                            'fdsnotacredito'
                                        ]);
            
            foreach($fdss as $posicionFds => $fds){
                
                $sfss = sfssubsidiosfacturassi::where('fdsid', $fds->fdsid)->get();

                $fdss[$posicionFds]['sfs'] = $sfss;
            }

            $fsis[$posicionFsi]['fds'] = $fdss;
        }

        $requestsalida = response()->json([
            "datos" => $fsis
        ]);

        return $requestsalida;

    }
}
