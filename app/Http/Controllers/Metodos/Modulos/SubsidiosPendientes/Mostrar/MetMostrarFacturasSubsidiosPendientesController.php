<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fdsfacturassidetalles;
use App\Models\ndsnotascreditossidetalles;

class MetMostrarFacturasSubsidiosPendientesController extends Controller
{
    public function MetMostrarFacturasSubsidiosPendientes(Request $request)
    {
        $coddestinatario = $request['sdecodigodestinatario'];
        $coddestinatariodos = $request['sdecodigodestinatario'];
        $coddestinatariotres = $request['sdecodigodestinatario'];

        if($coddestinatario == "170209"){
            $coddestinatario = "170418";
            $coddestinatariodos = "170418";
            $coddestinatariotres = "170418";
        }else if($coddestinatario == "278981"){
            $coddestinatario = "284861";
            $coddestinatariodos = "278982";
            $coddestinatariotres = "278981";

        }else if($coddestinatario == "96444"){
            $coddestinatario = "96474";
            $coddestinatariotres = "96474";
        }

        $fsis = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                ->join('proproductos as pro', 'pro.proid', 'fdsfacturassidetalles.proid')
                                // ->orwhere('fsi.fsidestinatario', $coddestinatario)
                                ->where('fsi.fsidestinatario', $coddestinatariodos)
                                // ->orwhere('fsi.fsidestinatario', $coddestinatariotres)
                                // ->where('fdssaldo', '>', 0)
                                ->orderBy('fdssaldo', 'desc')
                                ->get([
                                    'fdsfacturassidetalles.fdsid',
                                    'fdsfacturassidetalles.fsiid',
                                    // 'fecfecha',
                                    'fsifecha as fecfecha',
                                    'fsifactura',
                                    'fdsmaterial',
                                    'pro.proid',
                                    'prosku',
                                    'pronombre',
                                    'fdsvalorneto',
                                    'fdssaldo',
                                    'fdsnotacredito',
                                    'fsipedido',
                                    'fdsreconocer',
                                    'fsipedidooriginal'
                                ]);

        foreach($fsis as $posicionFsi => $fsi){
            $ndsSuma = ndsnotascreditossidetalles::where('ndspedidooriginal', $fsi->fsipedidooriginal)
                                                ->where('ndsmaterial', $fsi->fdsmaterial)
                                                ->sum('ndsvalorneto');

            $sumanotascredito = abs($ndsSuma); // VUELVE EL NÚMERO EN POSITIVO
            $fsis[$posicionFsi]['fdsnotacredito'] = $sumanotascredito;

            $reconocido = $fsi->fdsreconocer + $sumanotascredito;
            
            $saldosin = $fsi->fdsvalorneto * 30/100;

            $nuevoSaldo = $saldosin - $reconocido;

            if($nuevoSaldo < 0){
                $fsis[$posicionFsi]['fdssaldo'] = 0;
            }else{
                $fsis[$posicionFsi]['fdssaldo'] = $nuevoSaldo;
            }
        }

        $requestsalida = response()->json([
            "datos" => $fsis
        ]);

        return $requestsalida;


    }
}
