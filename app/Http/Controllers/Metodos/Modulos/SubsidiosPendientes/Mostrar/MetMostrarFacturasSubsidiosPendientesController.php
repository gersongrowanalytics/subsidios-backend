<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fdsfacturassidetalles;
use App\Models\ndsnotascreditossidetalles;
use App\Models\cliclientes;

class MetMostrarFacturasSubsidiosPendientesController extends Controller
{
    public function MetMostrarFacturasSubsidiosPendientes(Request $request)
    {
        $coddestinatario       = $request['sdecodigodestinatario'];
        $coddestinatariodos    = $request['sdecodigodestinatario'];
        $coddestinatariotres   = $request['sdecodigodestinatario'];
        $coddestinatariocuatro = $request['sdecodigodestinatario'];
        $coddestinatariocinco  = $request['sdecodigodestinatario'];

        $coddestinatarioseis   = $request['sdecodigodestinatario'];
        $coddestinatariosiete  = $request['sdecodigodestinatario'];
        $coddestinatarioocho   = $request['sdecodigodestinatario'];
        $coddestinatarionueve  = $request['sdecodigodestinatario'];
        $coddestinatariodiez   = $request['sdecodigodestinatario'];
        $coddestinatarioonce   = $request['sdecodigodestinatario'];

        $tieneDestinatarios = true;

        if($coddestinatario == "170209"){
            $coddestinatario = "170418";
            $coddestinatariodos = "170418";
            $coddestinatariotres = "170418";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "278981"){
            $coddestinatario = "284861";
            $coddestinatariodos = "278982";
            $coddestinatariotres = "278981";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "96444"){
            $coddestinatario = "96474";
            $coddestinatariodos = "96474";
            $coddestinatariotres = "96474";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "287493"){
            $coddestinatario = "289634";
            $coddestinatariodos = "289634";
            $coddestinatariotres = "289634";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "143397"){
            $coddestinatario = "143398";
            $coddestinatariodos = "143398";
            $coddestinatariotres = "143398";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "160864"){ //
            $coddestinatario = "148921";
            $coddestinatariodos = "148921";
            $coddestinatariotres = "148921";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "166945"){ //
            $coddestinatario = "146628";
            $coddestinatariodos = "151379";
            $coddestinatariotres = "151379";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "144124"){
            $coddestinatario = "108016";
            $coddestinatariodos = "168098";
            $coddestinatariotres = "241834";
            $coddestinatariocuatro = "252650";
            $coddestinatariocinco  = "80133";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "130157"){
            $coddestinatario = "271678";
            $coddestinatariodos = "271678";
            $coddestinatariotres = "271678";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "54237"){
            $coddestinatario = "54239";
            $coddestinatariodos = "171802";
            $coddestinatariotres = "171802";
            $tieneDestinatarios = true;
        }else if($coddestinatario == "168098"){
            
            $coddestinatario = "168098";
            $coddestinatariodos = "108016";
            $coddestinatariotres = "144124";
            $coddestinatariocuatro = "241834";
            $coddestinatariocinco  = "252649";

            $coddestinatarioseis  = "252650";
            $coddestinatariosiete  = "143070";
            $coddestinatarioocho  = "116986";
            $coddestinatarionueve  = "138113";
            $coddestinatariodiez  = "270535";
            $coddestinatarioonce  = "80133";

            $tieneDestinatarios = true;
        }

        $cli = cliclientes::where('clicodigoshipto', $coddestinatario)->first();

        $fsis = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                ->join('cliclientes as cli', 'cli.cliid', 'fdsfacturassidetalles.cliid')
                                ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                ->join('proproductos as pro', 'pro.proid', 'fdsfacturassidetalles.proid')
                                ->where('cli.clicodigo', $cli->clicodigo)
                                ->where('cli.clibloqueado', false)
                                ->where(function ($query) use($cli) {
                                    // if(isset($cli->cliclientegrupo)){
                                        $query->where('cliclientegrupo', $cli->cliclientegrupo);
                                    // }
                                })
                                // ->where(function ($query) use(
                                //     $tieneDestinatarios,
                                //     $coddestinatario,
                                //     $coddestinatariodos,
                                //     $coddestinatariotres,
                                //     $coddestinatariocuatro,
                                //     $coddestinatariocinco,
                                //     $coddestinatarioseis,
                                //     $coddestinatariosiete,
                                //     $coddestinatarioocho,
                                //     $coddestinatarionueve,
                                //     $coddestinatariodiez,
                                //     $coddestinatarioonce) {
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatario);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatariodos);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatariotres);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatariocuatro);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatariocinco);

                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatarioseis);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatariosiete);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatarioocho);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatarionueve);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatariodiez);
                                //     $query->orwhere('fsi.fsidestinatario', $coddestinatarioonce);
                                // })
                                // ->orwhere('fsi.fsidestinatario', $coddestinatariodos)
                                // ->orwhere('fsi.fsidestinatario', $coddestinatariotres)
                                // ->orwhere('fsi.fsidestinatario', $coddestinatariocuatro)
                                // ->orwhere('fsi.fsidestinatario', $coddestinatariocinco)
                                ->where('fdssaldo', '>', 0)
                                ->where('fsiclase', '!=', 'ZPF9')
                                ->where('fdsanulada', false)
                                ->where('fsisunataprobado', true)
                                ->orderBy('fdssaldo', 'desc')
                                // ->limit(50)
                                ->get([
                                    'fdsfacturassidetalles.fdsid',
                                    'fdsfacturassidetalles.fsiid',
                                    'fecanionumero',
                                    'fecmesabreviacion',
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
                                    'fsipedidooriginal',
                                    'fsiclase',
                                    'fsidestinatario',
                                    'fsisolicitante',
                                    'cliclientegrupo',
                                    'clicodigoshipto'
                                ]);
        
        $nuevoFsis = array();
        
        foreach($fsis as $posicionFsi => $fsi){
            if($fsi->fsiclase != "ZPF9"){
                $ndsSuma = ndsnotascreditossidetalles::where('ndspedidooriginal', $fsi->fsipedidooriginal)
                                                    ->where('ndsmaterial', $fsi->fdsmaterial)
                                                    ->sum('ndsvalorneto');

                $sumanotascredito = abs($ndsSuma); // VUELVE EL NÚMERO EN POSITIVO
                $fsis[$posicionFsi]['fdsnotacredito'] = $sumanotascredito;

                $reconocido = $fsi->fdsreconocer + $sumanotascredito;
                
                $saldosin = $fsi->fdsvalorneto * 30/100;

                $nuevoSaldo = $saldosin - $reconocido;

                if($nuevoSaldo < 0){
                    $nuevoSaldo = 0;
                    $fsis[$posicionFsi]['fdssaldo'] = 0;
                }else{
                    $fsis[$posicionFsi]['fdssaldo'] = $nuevoSaldo;
                }

                $nuevoSaldo = floatval($nuevoSaldo);

                if($nuevoSaldo > 1){
                    $nuevoFsis[] = array(
                        "fdsid" => $fsi->fdsid,
                        "fsiid" => $fsi->fsiid,
                        "fecfecha" => $fsi->fecfecha,
                        "fsifactura" => $fsi->fsifactura,
                        "fdsmaterial" => $fsi->fdsmaterial,
                        "proid" => $fsi->proid,
                        "prosku" => $fsi->prosku,
                        "pronombre" => $fsi->pronombre,
                        "fdsvalorneto" => $fsi->fdsvalorneto,
                        "fdssaldo" => $nuevoSaldo,
                        "fdsnotacredito" => $sumanotascredito,
                        "fsipedido" => $fsi->fsipedido,
                        "fdsreconocer" => $fsi->fdsreconocer,
                        "fsipedidooriginal" => $fsi->fsipedidooriginal,
                        "fsiclase" => $fsi->fsiclase,
                        "fsisolicitante" => $fsi->fsisolicitante,
                        "fsidestinatario" => $fsi->fsidestinatario,
                        "fecanionumero" => $fsi->fecanionumero,
                        "fecmesabreviacion" => $fsi->fecmesabreviacion,
                        "cliclientegrupo" => $fsi->cliclientegrupo,
                        "clicodigoshipto" => $fsi->clicodigoshipto,
                    );
                }

            }
        }


        usort(
            $nuevoFsis,
            function ($a, $b)  {
                if ($a['fdssaldo'] > $b['fdssaldo']) {
                    return -1;
                } else if ($a['fdssaldo'] < $b['fdssaldo']) {
                    return 1;
                } else {
                    return 0;
                }
            }
        );


        $requestsalida = response()->json([
            "datos" => $nuevoFsis
        ]);

        return $requestsalida;


    }
}
