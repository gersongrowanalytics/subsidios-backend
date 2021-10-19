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
        $coddestinatario       = $request['sdecodigodestinatario'];
        $coddestinatariodos    = $request['sdecodigodestinatario'];
        $coddestinatariotres   = $request['sdecodigodestinatario'];
        $coddestinatariocuatro = $request['sdecodigodestinatario'];
        $coddestinatariocinco  = $request['sdecodigodestinatario'];

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
            $coddestinatariodos = "96474";
            $coddestinatariotres = "96474";
        }else if($coddestinatario == "287493"){
            $coddestinatario = "289634";
            $coddestinatariodos = "289634";
            $coddestinatariotres = "289634";
        }else if($coddestinatario == "143397"){
            $coddestinatario = "143398";
            $coddestinatariodos = "143398";
            $coddestinatariotres = "143398";
        }else if($coddestinatario == "160864"){
            $coddestinatario = "148921";
            $coddestinatariodos = "148921";
            $coddestinatariotres = "148921";
        }else if($coddestinatario == "166945"){
            $coddestinatario = "146628";
            $coddestinatariodos = "151379";
            $coddestinatariotres = "151379";
        }else if($coddestinatario == "144124"){
            $coddestinatario = "108016";
            $coddestinatariodos = "168098";
            $coddestinatariotres = "241834";
            $coddestinatariocuatro = "252650";
            $coddestinatariocinco  = "80133";
        }else if($coddestinatario == "130157"){
            $coddestinatario = "271678";
            $coddestinatariodos = "271678";
            $coddestinatariotres = "271678";
        }else if($coddestinatario == "54237"){
            $coddestinatario = "54239";
            $coddestinatariodos = "171802";
            $coddestinatariotres = "171802";
        }

        $fsis = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                ->join('proproductos as pro', 'pro.proid', 'fdsfacturassidetalles.proid')
                                ->orwhere('fsi.fsidestinatario', $coddestinatario)
                                ->orwhere('fsi.fsidestinatario', $coddestinatariodos)
                                ->orwhere('fsi.fsidestinatario', $coddestinatariotres)
                                ->orwhere('fsi.fsidestinatario', $coddestinatariocuatro)
                                ->orwhere('fsi.fsidestinatario', $coddestinatariocinco)
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
