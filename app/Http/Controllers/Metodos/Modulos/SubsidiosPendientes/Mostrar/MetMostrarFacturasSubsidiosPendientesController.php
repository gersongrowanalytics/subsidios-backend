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


        $fsis = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                ->join('proproductos as pro', 'pro.proid', 'fdsfacturassidetalles.proid')
                                ->where('fsi.fsidestinatario', $coddestinatario)
                                ->where('fdssaldo', '>', 0)
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
