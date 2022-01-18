<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use App\Models\usuusuarios;
use App\Models\sfssubsidiosfacturassi;
use App\Models\fecfechas;
use App\Models\sdesubsidiosdetalles;
use App\Models\fdsfacturassidetalles;
use App\Models\ndsnotascreditossidetalles;
use App\Models\cliclientes;


class MetLogicaSubsidiosSiController extends Controller
{
    public function MetLogicaSubsidiosSi(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "NO_SE_ENCONTRARON_FACTURAS" => []
        );

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "";
        $datos          = [];
        $mensajeDetalle = "";

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

        $fecid = $request['fecid'];

        $fec = fecfechas::find($fecid);

        if($fec){
            $mesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 1 month")))->first();
            $dosMesesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 2 month")))->first();
            $tresMesesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 3 month")))->first();

            $mesAnteriorId = $mesAnterior->fecid;
            $dosMesesAnteriorId = $dosMesesAnterior->fecid;
            $tresMesesAnteriorId = $tresMesesAnterior->fecid;

            $meses = [$mesAnteriorId, $dosMesesAnteriorId, $tresMesesAnteriorId];

            // REINICIAR
            // $this->ActualizarReconocimientoSaldosFacturas($fecid);

            $sdes = sdesubsidiosdetalles::join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                        ->join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                        ->where('fecid', $fecid)
                                        ->where('sdeaprobado', true)
                                        ->where('sdemontoareconocerreal', '!=',0)
                                        ->get([
                                            'sdeid',
                                            'pro.proid',
                                            'pro.prosku',
                                            'fecid',
                                            'cli.cliid',
                                            'cli.clicodigo',
                                            'sdemontoareconocerreal',
                                            'sdemontoacido',
                                            'clibloqueado'
                                        ]);

            foreach($sdes as $sde){

                $idFacturaEncontrada = [];
                $facturasAfectadas = array();

                $dataObtenida = array(
                    "idFacturaEncontrada" => [],
                    "esPendiente" => false
                );

                $cliidbuscar = $sde->cliid;
                    
                if($sde->clibloqueado == true){
                    $cliidbuscar = "0";
                }

                $dataObtenida = $this->BuscarFacturas(
                    $idFacturaEncontrada, 
                    $sde->proid, 
                    $fecid, 
                    // $sde->cliid, 
                    $cliidbuscar, 
                    // $sde->sdemontoareconocerreal,
                    $sde->sdemontoacido,
                    $meses, 
                    $facturasAfectadas,

                );

                $idFacturaEncontrada = $dataObtenida["idFacturaEncontrada"];
                $facturasAfectadas   = $dataObtenida["facturasAfectadas"];

                if(sizeof($idFacturaEncontrada) > 0){

                    // $montoReconocerReal = $sde->sdemontoareconocerreal;
                    $montoReconocerReal = $sde->sdemontoacido;

                    foreach($facturasAfectadas as $facturaAfectada){
                        $fds = fdsfacturassidetalles::find($facturaAfectada['id']);

                        $sfsn = new sfssubsidiosfacturassi;
                        $sfsn->fecid = $fecid;
                        $sfsn->sdeid = $sde->sdeid;
                        $sfsn->fsiid = $fds->fsiid;
                        $sfsn->fdsid = $facturaAfectada['id'];
                        $sfsn->nsiid = null;
                        $sfsn->ndsid = null;
                        $sfsn->sfsvalorizado = $facturaAfectada['valorizado'];

                        $sfsn->sfssaldoanterior = $facturaAfectada['saldoAnterior'];
                        $sfsn->sfssaldonuevo    = $facturaAfectada['saldoNuevo'];

                        $sfsn->sfsobjetivo = $facturaAfectada['objetivoActual'];
                        $sfsn->sfsdiferenciaobjetivo = $facturaAfectada['nuevoObjetivo'];

                        $sfsn->save();
                    }

                    if($dataObtenida["esPendiente"] == true){
                        $logs["SUBSIDIOS_PENDIENTES"][] = "Subsidios pendientes al sde: ".$sde->sdeid;
                    }else{
                        $logs["SUBSIDIOS"][] = "Subsidios sde: ".$sde->sdeid;
                    }

                    $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                    $sdee->sdeencontrofactura = true;
                    $sdee->sdependiente = $dataObtenida["esPendiente"];
                    $sdee->update();

                }else{

                    if($dataObtenida["esPendiente"] == true){
                        $logs["SUBSIDIOS_PENDIENTES"][] = "Subsidios pendientes al sde: ".$sde->sdeid;
                    }else{
                        $logs["SUBSIDIOS"][] = "Subsidios sde: ".$sde->sdeid;
                    }

                    $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                    $sdee->sdependiente = $dataObtenida["esPendiente"];
                    $sdee->update();

                    $logs["NO_SE_ENCONTRARON_FACTURAS"][] = "No se encontro facturas para asignar al sde: ".$sde->sdeid;
                }

            }

        }else{
            return $fecid;
        }


        $logs["MENSAJE"] = $mensaje;

        $requestsalida = response()->json([
            "respuesta"      => $respuesta,
            "mensaje"        => $mensaje,
            "datos"          => $datos,
            "mensajeDetalle" => $mensajeDetalle,
            "logs" => $logs,
            "meses" => $meses
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken, // token
            $usu->usuid, // usuid
            null, // audip
            $request, // audjsonentrada
            $requestsalida,// audjsonsalida
            'EJECUTAR LOGICA PARA SELECCIONAR FACTURAS A LOS SUBSIDIOS SO EN EL MES: '.$fecid, //auddescripcion
            'EJECUTAR', // audaccion
            '/modulo/SubsidiosSi/logica', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;


    }

    public function ActualizarReconocimientoSaldosFacturas($fecid)
    {
        sfssubsidiosfacturassi::where('fecid', $fecid)
                                ->delete();

        sdesubsidiosdetalles::where('fecid', $fecid)
                            ->where('sdeaprobado', true)
                            ->update([
                                "sdependiente" => 0,
                                "sdeencontrofactura" => 0
                            ]);
            
        // $sfss = sfssubsidiosfacturassi::get([
        //                                     'sfsid',
        //                                     'sdeid',
        //                                     'fdsid'
        //                                 ]);

        // foreach($sfss as $fds){
        //     $fdse = fdsfacturassidetalles::find($fds->fdsid);
        //     if($fdse){
        //         $fdse->fdsreconocer   = 0;
        //         $fdse->fdsnotacredito = 0;
        //         $fdse->fdssaldo     = $fdse->fdstreintaporciento;
        //         $fdse->update();
        //     }
        // }                                


        // $fdss = fdsfacturassidetalles::get(['fdsid']);
        // $fdss = fdsfacturassidetalles::where('fecid', '>', '1096')
        //                                 ->get();

        // foreach($fdss as $fds){
        //     $fdse = fdsfacturassidetalles::find($fds->fdsid);
        //     $fdse->fdsreconocer   = 0;
        //     $fdse->fdsnotacredito = 0;
        //     $fdse->fdssaldo     = $fdse->fdstreintaporciento;
        //     $fdse->update();
        // }

        //REINICIAMOS TODO EL FDS, EL FDSRECONOCER PODEMOS OBTENERLO SUMANDO SFS, EL FDSNOTACREDITO DE LAS NOTAS DE CREDITO DE LA DATA SUBIDA

    }

    public function BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas)
    {


        $espendiente = true;

        $fds = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                    // ->where('fdsfacturassidetalles.proid', $proid)
                                    ->where('fdsfacturassidetalles.proid', $proid)
                                    ->where('fdsfacturassidetalles.fecid', $fecid)
                                    ->where('fdsfacturassidetalles.cliid', $cliid)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where('fdsanulada', 0)
                                    ->where('fdssaldo', '>', 0.10)
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->where('fsiclase', '!=', "ZPF9")
                                    ->where('fsisunataprobado', 1)
                                    ->first([
                                        'fdsfacturassidetalles.fdsid',
                                        'fdsnotacredito',
                                        'fdstreintaporciento',
                                        'fdsreconocer',
                                        'fdssaldo',
                                        'fdspedido'
                                    ]);

        if($fds){

            $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                            ->where('proid', $proid)
                                                            ->where('ndsanulada', 0)
                                                            ->sum('ndsvalorneto'); // DATO EN NEGATIVO

            $sumanotascredito = abs($sumanotascredito); // VUELVE EL NÚMERO EN POSITIVO

            $editarSaldo = false;
            if($fds->fdsnotacredito != $sumanotascredito){
                $fds->fdsnotacredito = $sumanotascredito;
                $editarSaldo = true;
            }
            
            if($editarSaldo == true){
                if($fds->fdstreintaporciento >= $sumanotascredito){
                    $nuevoSaldo = $fds->fdstreintaporciento - $sumanotascredito;
    
                    $fds->fdsreconocer = $sumanotascredito;
    
                }else{
    
                    $sumanotascredito = $sumanotascredito - $fds->fdstreintaporciento;
    
                    $fds->fdsreconocer = $fds->fdstreintaporciento;
    
                    $nuevoSaldo = 0;
                }
    
                $fds->fdssaldo = $nuevoSaldo;
            }


            if($sdemontoareconocerreal <=  $fds->fdssaldo){
                $idFacturaEncontrada[] = $fds->fdsid;
                $facturasAfectadas[] = array(
                    "id" => $fds->fdsid,
                    "valorizado" => $sdemontoareconocerreal,
                    "saldoAnterior" => $fds->fdssaldo,
                    "saldoNuevo" => $fds->fdssaldo - $sdemontoareconocerreal,
                    "objetivoActual" => $sdemontoareconocerreal,
                    "nuevoObjetivo" => 0
                );


                $fds->fdssaldo = $fds->fdssaldo - $sdemontoareconocerreal;
                $fds->fdsreconocer = $fds->fdsreconocer + $sdemontoareconocerreal;
                $fds->update();

                $espendiente = false;

            }else{
                $idFacturaEncontrada[] = $fds->fdsid;
                if($fds->fdssaldo > 0){
                    $facturasAfectadas[] = array(
                        "id" => $fds->fdsid,
                        "valorizado" => $fds->fdssaldo,
                        "saldoAnterior" => $fds->fdssaldo,
                        "saldoNuevo" => 0,
                        "objetivoActual" => $sdemontoareconocerreal,
                        "nuevoObjetivo" => $sdemontoareconocerreal - $fds->fdssaldo
                    );
                }

                $sdemontoareconocerreal = $sdemontoareconocerreal - $fds->fdssaldo;
                $fds->fdsreconocer = $fds->fdsreconocer + $fds->fdssaldo;
                $fds->fdssaldo = 0;
                $fds->update();
                // $idFacturaEncontrada = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses);
                $dat = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas);
                $idFacturaEncontrada = $dat["idFacturaEncontrada"];
                $espendiente = $dat["esPendiente"];
                $facturasAfectadas = $dat["facturasAfectadas"];
            }

        }else{

            $encontrofactura = false;

            foreach($meses as $mes){

                $fds = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                    ->where('fdsfacturassidetalles.proid', $proid)
                                    ->where('fdsfacturassidetalles.fecid', $mes)
                                    ->where('fdsfacturassidetalles.cliid', $cliid)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where('fdsanulada', 0)
                                    ->where('fdssaldo', '>', 0.10)
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->where('fsiclase', '!=', "ZPF9")
                                    ->where('fsisunataprobado', 1)
                                    ->first([
                                        'fdsfacturassidetalles.fdsid',
                                        'fdsnotacredito',
                                        'fdstreintaporciento',
                                        'fdsreconocer',
                                        'fdssaldo',
                                        'fdspedido'
                                    ]);

                if($fds){
                    
                    // $idFacturaEncontrada[] = $fds->fdsid;

                    $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                                    ->where('proid', $proid)
                                                                    ->where('ndsanulada', 0)
                                                                    ->sum('ndsvalorneto');

                    $sumanotascredito = abs($sumanotascredito);

                    $editarSaldo = false;
                    if($fds->fdsnotacredito != $sumanotascredito){
                        $fds->fdsnotacredito = $sumanotascredito;
                        $editarSaldo = true;
                    }

                    if($editarSaldo == true){
                        if($fds->fdstreintaporciento >= $sumanotascredito){
                            $nuevoSaldo = $fds->fdstreintaporciento - $sumanotascredito;
    
                            $fds->fdsreconocer = $sumanotascredito;
    
                        }else{
    
                            $sumanotascredito = $sumanotascredito - $fds->fdstreintaporciento;
    
                            $fds->fdsreconocer = $fds->fdstreintaporciento;
    
                            $nuevoSaldo = 0;
                        }
    
                        $fds->fdssaldo = $nuevoSaldo;
                    }

                    if($sdemontoareconocerreal <=  $fds->fdssaldo){
                        $idFacturaEncontrada[] = $fds->fdsid;
                        $facturasAfectadas[] = array(
                            "id" => $fds->fdsid,
                            "valorizado" => $sdemontoareconocerreal,
                            "saldoAnterior" => $fds->fdssaldo,
                            "saldoNuevo" => $fds->fdssaldo - $sdemontoareconocerreal,
                            "objetivoActual" => $sdemontoareconocerreal,
                            "nuevoObjetivo" => 0
                        );

                        $fds->fdssaldo = $fds->fdssaldo - $sdemontoareconocerreal;
                        $fds->fdsreconocer = $fds->fdsreconocer + $sdemontoareconocerreal;
                        $fds->update();

                        $espendiente = false;
                    }else{
                        $idFacturaEncontrada[] = $fds->fdsid;
                        if($fds->fdssaldo > 0){
                            $facturasAfectadas[] = array(
                                "id" => $fds->fdsid,
                                "valorizado" => $fds->fdssaldo,
                                "saldoAnterior" => $fds->fdssaldo,
                                "saldoNuevo" => 0,
                                "objetivoActual" => $sdemontoareconocerreal,
                                "nuevoObjetivo" => $sdemontoareconocerreal - $fds->fdssaldo
                            );
                        }

                        $sdemontoareconocerreal = $sdemontoareconocerreal - $fds->fdssaldo;
                        $fds->fdsreconocer = $fds->fdsreconocer + $fds->fdssaldo;
                        $fds->fdssaldo = 0;
                        $fds->update();
                        // $idFacturaEncontrada = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses);
                        $dat = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas);
                        $idFacturaEncontrada = $dat["idFacturaEncontrada"];
                        $espendiente = $dat["esPendiente"];
                        $facturasAfectadas = $dat["facturasAfectadas"];
                    }

                    // if($fds->update()){
                        
                        // $fdse = fdsfacturassidetalles::find($fds->fdsid);

                        
                        
                    // }

                    $encontrofactura = true;
                    break;
                }

            }

            if($encontrofactura == false){
                
                // $espendiente = true;

                $fds = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                    ->where('fdsfacturassidetalles.proid', $proid)
                                    // ->where('fecid', $mes)
                                    ->where('fdsfacturassidetalles.cliid', $cliid)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where('fdsanulada', 0)
                                    ->where('fdssaldo', '>', 0.10)
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->where('fsiclase', '!=', "ZPF9")
                                    ->where('fsisunataprobado', 1)
                                    ->first([
                                        'fdsfacturassidetalles.fdsid',
                                        'fdsnotacredito',
                                        'fdstreintaporciento',
                                        'fdsreconocer',
                                        'fdssaldo',
                                        'fdspedido'
                                    ]);

                if($fds){
                    
                    // $idFacturaEncontrada = $fds->fdsid;

                    $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                                    ->where('proid', $proid)
                                                                    ->where('ndsanulada', 0)
                                                                    ->sum('ndsvalorneto');
                    
                    $sumanotascredito = abs($sumanotascredito);

                    $editarSaldo = false;
                    if($fds->fdsnotacredito != $sumanotascredito){
                        $fds->fdsnotacredito = $sumanotascredito;
                        $editarSaldo = true;
                    }

                    if($editarSaldo == true){
                        if($fds->fdstreintaporciento >= $sumanotascredito){
                            $nuevoSaldo = $fds->fdstreintaporciento - $sumanotascredito;
    
                            $fds->fdsreconocer = $sumanotascredito;
    
                        }else{
    
                            $sumanotascredito = $sumanotascredito - $fds->fdstreintaporciento;
    
                            $fds->fdsreconocer = $fds->fdstreintaporciento;
    
                            $nuevoSaldo = 0;
                        }
                        $fds->fdssaldo = $nuevoSaldo;
                    }

                    if($sdemontoareconocerreal <=  $fds->fdssaldo){
                        $idFacturaEncontrada[] = $fds->fdsid;
                        $facturasAfectadas[] = array(
                            "id" => $fds->fdsid,
                            "valorizado" => $sdemontoareconocerreal,
                            "saldoAnterior" => $fds->fdssaldo,
                            "saldoNuevo" => $fds->fdssaldo - $sdemontoareconocerreal,
                            "objetivoActual" => $sdemontoareconocerreal,
                            "nuevoObjetivo" => 0
                        );

                        $fds->fdssaldo = $fds->fdssaldo - $sdemontoareconocerreal;
                        $fds->fdsreconocer = $fds->fdsreconocer + $sdemontoareconocerreal;
                        $fds->update();

                        $espendiente = false;

                    }else{
                        $idFacturaEncontrada[] = $fds->fdsid;
                        if($fds->fdssaldo > 0){
                            $facturasAfectadas[] = array(
                                "id" => $fds->fdsid,
                                "valorizado" => $fds->fdssaldo,
                                "saldoAnterior" => $fds->fdssaldo,
                                "saldoNuevo" => 0,
                                "objetivoActual" => $sdemontoareconocerreal,
                                "nuevoObjetivo" => $sdemontoareconocerreal - $fds->fdssaldo
                            );
                        }

                        $sdemontoareconocerreal = $sdemontoareconocerreal - $fds->fdssaldo;
                        $fds->fdsreconocer = $fds->fdsreconocer + $fds->fdssaldo;
                        $fds->fdssaldo = 0;
                        $fds->update();
                        // $idFacturaEncontrada = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses);
                        $dat = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas);
                        $idFacturaEncontrada = $dat["idFacturaEncontrada"];
                        $espendiente = $dat["esPendiente"];
                        $facturasAfectadas = $dat["facturasAfectadas"];
    
                    }

                    // if($fds->update()){

                        // $fdse = fdsfacturassidetalles::find($fds->fdsid);

                        
                    // }
                    // break;
                }


            }
        }

        $dataObtenida = array(
            "idFacturaEncontrada" => $idFacturaEncontrada,
            "esPendiente" => $espendiente,
            "facturasAfectadas" => $facturasAfectadas
        );

        // return $idFacturaEncontrada;
        return $dataObtenida;

    }

    // LOGICA POR SOLIC A SUBSIDIOS PENDIENTES

    public function MetLogicaSubsidiosSiSolic(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "NO_SE_ENCONTRARON_FACTURAS" => []
        );

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "";
        $datos          = [];
        $mensajeDetalle = "";

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

        $fecid = $request['fecid'];

        $fec = fecfechas::find($fecid);

        if($fec){
            $mesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 1 month")))->first();
            $dosMesesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 2 month")))->first();
            $tresMesesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 3 month")))->first();

            $mesAnteriorId = $mesAnterior->fecid;
            $dosMesesAnteriorId = $dosMesesAnterior->fecid;
            $tresMesesAnteriorId = $tresMesesAnterior->fecid;

            $meses = [$mesAnteriorId, $dosMesesAnteriorId, $tresMesesAnteriorId];

            // REINICIAR
            // $this->ActualizarReconocimientoSaldosFacturas($fecid);

            $sdes = sdesubsidiosdetalles::join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                        ->join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                        ->where('fecid', $fecid)
                                        ->where('sdeaprobado', true)
                                        ->where('sdemontoareconocerreal', '!=',0)
                                        ->where('sdependiente', true) // SOLO INCLUIR EN "BUSCARFACTURASOLIC"
                                        // ->where('sdeid', 99887)
                                        ->get([
                                            'sdeid',
                                            'pro.proid',
                                            'pro.prosku',
                                            'fecid',
                                            'cli.cliid',
                                            'cli.clicodigo',
                                            'sdemontoareconocerreal',
                                            'sdemontoacido',
                                            'sdecodigosolicitante',
                                            'cliclientegrupo'
                                        ]);

            foreach($sdes as $sde){

                $idFacturaEncontrada = [];
                $facturasAfectadas = array();

                $dataObtenida = array(
                    "idFacturaEncontrada" => [],
                    "esPendiente" => false
                );

                $sumSfsvalorizado = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)
                                                        ->sum('sfsvalorizado');

                // $montoAReconocer = $sde->sdemontoareconocerreal - $sumSfsvalorizado;
                $montoAReconocer = $sde->sdemontoacido - $sumSfsvalorizado;

                if($montoAReconocer > 0){
                    // $dataObtenida = $this->BuscarFacturas(
                    
                    $cli = cliclientes::where('clicodigoshipto', $sde->sdecodigosolicitante)->first();
                    
                    if($cli->clibloqueado == true){
                        $codigoCliente = "0";
                    }else{
                        $codigoCliente = $sde->sdecodigosolicitante;
                    }

                    $dataObtenida = $this->BuscarFacturasSolic(
                        $idFacturaEncontrada, 
                        $sde->proid, 
                        $fecid, 
                        $sde->cliid, 
                        // $sde->sdemontoareconocerreal, 
                        $montoAReconocer, 
                        $meses, 
                        $facturasAfectadas,

                        $codigoCliente, // SOLO INCLUIR EN "BUSCARFACTURASOLIC"
                        $sde->cliclientegrupo
                    );

                    $idFacturaEncontrada = $dataObtenida["idFacturaEncontrada"];
                    $facturasAfectadas   = $dataObtenida["facturasAfectadas"];

                    if(sizeof($idFacturaEncontrada) > 0){

                        // $montoReconocerReal = $sde->sdemontoareconocerreal;
                        $montoReconocerReal = $sde->sdemontoacido;

                        foreach($facturasAfectadas as $facturaAfectada){
                            $fds = fdsfacturassidetalles::find($facturaAfectada['id']);

                            $sfsn = new sfssubsidiosfacturassi;
                            $sfsn->fecid = $fecid;
                            $sfsn->sdeid = $sde->sdeid;
                            $sfsn->fsiid = $fds->fsiid;
                            $sfsn->fdsid = $facturaAfectada['id'];
                            $sfsn->nsiid = null;
                            $sfsn->ndsid = null;
                            $sfsn->sfsvalorizado = $facturaAfectada['valorizado'];

                            $sfsn->sfssaldoanterior = $facturaAfectada['saldoAnterior'];
                            $sfsn->sfssaldonuevo    = $facturaAfectada['saldoNuevo'];

                            $sfsn->sfsobjetivo = $facturaAfectada['objetivoActual'];
                            $sfsn->sfsdiferenciaobjetivo = $facturaAfectada['nuevoObjetivo'];
                            $sfsn->sfslogicasolicitante = true;
                            $sfsn->save();
                        }

                        if($dataObtenida["esPendiente"] == true){
                            $logs["SUBSIDIOS_PENDIENTES"][] = "Subsidios pendientes al sde: ".$sde->sdeid;
                        }else{
                            $logs["SUBSIDIOS"][] = "Subsidios sde: ".$sde->sdeid;
                        }

                        $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                        $sdee->sdeencontrofactura = true;
                        $sdee->sdependiente = $dataObtenida["esPendiente"];
                        $sdee->sdelogicasolicitante = true;
                        $sdee->update();

                    }else{

                        if($dataObtenida["esPendiente"] == true){
                            $logs["SUBSIDIOS_PENDIENTES"][] = "Subsidios pendientes al sde: ".$sde->sdeid;
                        }else{
                            $logs["SUBSIDIOS"][] = "Subsidios sde: ".$sde->sdeid;
                        }

                        $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                        $sdee->sdependiente = $dataObtenida["esPendiente"];
                        $sdee->sdelogicasolicitante = true;
                        $sdee->update();

                        $logs["NO_SE_ENCONTRARON_FACTURAS"][] = "No se encontro facturas para asignar al sde: ".$sde->sdeid;
                    }
                }

            }

        }else{
            return $fecid;
        }


        $logs["MENSAJE"] = $mensaje;

        $requestsalida = response()->json([
            "respuesta"      => $respuesta,
            "mensaje"        => $mensaje,
            "datos"          => $datos,
            "mensajeDetalle" => $mensajeDetalle,
            "logs" => $logs,
            "meses" => $meses
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken, // token
            $usu->usuid, // usuid
            null, // audip
            $request, // audjsonentrada
            $requestsalida,// audjsonsalida
            'EJECUTAR LOGICA PARA SELECCIONAR FACTURAS A LOS SUBSIDIOS SO EN EL MES: '.$fecid, //auddescripcion
            'EJECUTAR', // audaccion
            '/modulo/SubsidiosSi/logica', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;


    }

    public function BuscarFacturasSolic($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas, $sdecodigosolicitante, $cliclientegrupo)
    {


        $espendiente = true;

        $fds = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                    ->join('cliclientes as cli', 'cli.cliid', 'fdsfacturassidetalles.cliid')
                                    ->where('fdsfacturassidetalles.proid', $proid)
                                    ->where('fdsfacturassidetalles.fecid', $fecid)
                                    // ->where('fdsfacturassidetalles.cliid', $cliid)
                                    ->where('fsi.fsisolicitante', $sdecodigosolicitante)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where(function ($query) use($cliclientegrupo) {
                                        if(isset($cliclientegrupo)){
                                            $query->where('cliclientegrupo', $cliclientegrupo);
                                        }
                                    })
                                    ->where('fdsanulada', 0)
                                    ->where('fdssaldo', '>', 0.10)
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->where('fsiclase', '!=', "ZPF9")
                                    ->where('fsisunataprobado', 1)
                                    ->first([
                                        'fdsfacturassidetalles.fdsid',
                                        'fdsnotacredito',
                                        'fdstreintaporciento',
                                        'fdsreconocer',
                                        'fdssaldo',
                                        'fdspedido'
                                    ]);

        if($fds){

            $sumSfsvalorizado = sfssubsidiosfacturassi::where('fdsid', $fds->fdsid)
                                                ->where('fecid', $fecid)
                                                ->sum('sfsvalorizado');


            $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                            ->where('proid', $proid)
                                                            ->where('ndsanulada', 0)
                                                            ->sum('ndsvalorneto'); // DATO EN NEGATIVO

            $sumanotascredito = abs($sumanotascredito); // VUELVE EL NÚMERO EN POSITIVO

            $editarSaldo = false;
            if($fds->fdsnotacredito != $sumanotascredito){
                $fds->fdsnotacredito = $sumanotascredito;
                $editarSaldo = true;
            }
            
            $sumanotascredito = $sumanotascredito + $sumSfsvalorizado;
            $editarSaldo = true;

            if($editarSaldo == true){
                if($fds->fdstreintaporciento >= $sumanotascredito){
                    $nuevoSaldo = $fds->fdstreintaporciento - $sumanotascredito;
    
                    $fds->fdsreconocer = $sumanotascredito;
    
                }else{
    
                    $sumanotascredito = $sumanotascredito - $fds->fdstreintaporciento;
    
                    $fds->fdsreconocer = $fds->fdstreintaporciento;
    
                    $nuevoSaldo = 0;
                }
    
                $fds->fdssaldo = $nuevoSaldo;
            }


            if($sdemontoareconocerreal <=  $fds->fdssaldo){
                $idFacturaEncontrada[] = $fds->fdsid;
                $facturasAfectadas[] = array(
                    "id" => $fds->fdsid,
                    "valorizado" => $sdemontoareconocerreal,
                    "saldoAnterior" => $fds->fdssaldo,
                    "saldoNuevo" => $fds->fdssaldo - $sdemontoareconocerreal,
                    "objetivoActual" => $sdemontoareconocerreal,
                    "nuevoObjetivo" => 0
                );


                $fds->fdssaldo = $fds->fdssaldo - $sdemontoareconocerreal;
                $fds->fdsreconocer = $fds->fdsreconocer + $sdemontoareconocerreal;
                $fds->update();

                $espendiente = false;

            }else{
                $idFacturaEncontrada[] = $fds->fdsid;
                if($fds->fdssaldo > 0){
                    $facturasAfectadas[] = array(
                        "id" => $fds->fdsid,
                        "valorizado" => $fds->fdssaldo,
                        "saldoAnterior" => $fds->fdssaldo,
                        "saldoNuevo" => 0,
                        "objetivoActual" => $sdemontoareconocerreal,
                        "nuevoObjetivo" => $sdemontoareconocerreal - $fds->fdssaldo
                    );
                }

                $sdemontoareconocerreal = $sdemontoareconocerreal - $fds->fdssaldo;
                $fds->fdsreconocer = $fds->fdsreconocer + $fds->fdssaldo;
                $fds->fdssaldo = 0;
                $fds->update();
                // $idFacturaEncontrada = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses);
                $dat = $this->BuscarFacturasSolic($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas, $sdecodigosolicitante, $cliclientegrupo);
                $idFacturaEncontrada = $dat["idFacturaEncontrada"];
                $espendiente = $dat["esPendiente"];
                $facturasAfectadas = $dat["facturasAfectadas"];
            }

        }else{

            $encontrofactura = false;

            foreach($meses as $mes){

                $fds = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                    ->join('cliclientes as cli', 'cli.cliid', 'fdsfacturassidetalles.cliid')
                                    ->where('fdsfacturassidetalles.proid', $proid)
                                    ->where('fdsfacturassidetalles.fecid', $mes)
                                    // ->where('fdsfacturassidetalles.cliid', $cliid)
                                    ->where('fsi.fsisolicitante', $sdecodigosolicitante)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where(function ($query) use($cliclientegrupo) {
                                        if(isset($cliclientegrupo)){
                                            $query->where('cliclientegrupo', $cliclientegrupo);
                                        }
                                    })
                                    ->where('fdsanulada', 0)
                                    ->where('fdssaldo', '>', 0.10)
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->where('fsiclase', '!=', "ZPF9")
                                    ->where('fsisunataprobado', 1)
                                    ->first([
                                        'fdsfacturassidetalles.fdsid',
                                        'fdsnotacredito',
                                        'fdstreintaporciento',
                                        'fdsreconocer',
                                        'fdssaldo',
                                        'fdspedido'
                                    ]);

                if($fds){
                    
                    // $idFacturaEncontrada[] = $fds->fdsid;
                    $sumSfsvalorizado = sfssubsidiosfacturassi::where('fdsid', $fds->fdsid)
                                                ->where('fecid', $fecid)
                                                ->sum('sfsvalorizado');

                    $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                                    ->where('proid', $proid)
                                                                    ->where('ndsanulada', 0)
                                                                    ->sum('ndsvalorneto');

                    $sumanotascredito = abs($sumanotascredito);

                    $editarSaldo = false;
                    if($fds->fdsnotacredito != $sumanotascredito){
                        $fds->fdsnotacredito = $sumanotascredito;
                        $editarSaldo = true;
                    }

                    $sumanotascredito = $sumanotascredito + $sumSfsvalorizado;
                    $editarSaldo = true;

                    if($editarSaldo == true){
                        if($fds->fdstreintaporciento >= $sumanotascredito){
                            $nuevoSaldo = $fds->fdstreintaporciento - $sumanotascredito;
    
                            $fds->fdsreconocer = $sumanotascredito;
    
                        }else{
    
                            $sumanotascredito = $sumanotascredito - $fds->fdstreintaporciento;
    
                            $fds->fdsreconocer = $fds->fdstreintaporciento;
    
                            $nuevoSaldo = 0;
                        }
    
                        $fds->fdssaldo = $nuevoSaldo;
                    }

                    if($sdemontoareconocerreal <=  $fds->fdssaldo){
                        $idFacturaEncontrada[] = $fds->fdsid;
                        $facturasAfectadas[] = array(
                            "id" => $fds->fdsid,
                            "valorizado" => $sdemontoareconocerreal,
                            "saldoAnterior" => $fds->fdssaldo,
                            "saldoNuevo" => $fds->fdssaldo - $sdemontoareconocerreal,
                            "objetivoActual" => $sdemontoareconocerreal,
                            "nuevoObjetivo" => 0
                        );

                        $fds->fdssaldo = $fds->fdssaldo - $sdemontoareconocerreal;
                        $fds->fdsreconocer = $fds->fdsreconocer + $sdemontoareconocerreal;
                        $fds->update();

                        $espendiente = false;
                    }else{
                        $idFacturaEncontrada[] = $fds->fdsid;
                        if($fds->fdssaldo > 0){
                            $facturasAfectadas[] = array(
                                "id" => $fds->fdsid,
                                "valorizado" => $fds->fdssaldo,
                                "saldoAnterior" => $fds->fdssaldo,
                                "saldoNuevo" => 0,
                                "objetivoActual" => $sdemontoareconocerreal,
                                "nuevoObjetivo" => $sdemontoareconocerreal - $fds->fdssaldo
                            );
                        }

                        $sdemontoareconocerreal = $sdemontoareconocerreal - $fds->fdssaldo;
                        $fds->fdsreconocer = $fds->fdsreconocer + $fds->fdssaldo;
                        $fds->fdssaldo = 0;
                        $fds->update();
                        // $idFacturaEncontrada = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses);
                        $dat = $this->BuscarFacturasSolic($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas, $sdecodigosolicitante, $cliclientegrupo);
                        $idFacturaEncontrada = $dat["idFacturaEncontrada"];
                        $espendiente = $dat["esPendiente"];
                        $facturasAfectadas = $dat["facturasAfectadas"];
                    }

                    // if($fds->update()){
                        
                        // $fdse = fdsfacturassidetalles::find($fds->fdsid);

                        
                        
                    // }

                    $encontrofactura = true;
                    break;
                }

            }

            if($encontrofactura == false){
                
                // $espendiente = true;

                $fds = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                    ->join('cliclientes as cli', 'cli.cliid', 'fdsfacturassidetalles.cliid')
                                    ->where('fdsfacturassidetalles.proid', $proid)
                                    // ->where('fecid', $mes)
                                    // ->where('fdsfacturassidetalles.cliid', $cliid)
                                    ->where('fsi.fsisolicitante', $sdecodigosolicitante)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where(function ($query) use($cliclientegrupo) {
                                        if(isset($cliclientegrupo)){
                                            $query->where('cliclientegrupo', $cliclientegrupo);
                                        }
                                    })
                                    ->where('fdsanulada', 0)
                                    ->where('fdssaldo', '>', 0.10)
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->where('fsiclase', '!=', "ZPF9")
                                    ->where('fsisunataprobado', 1)
                                    ->first([
                                        'fdsfacturassidetalles.fdsid',
                                        'fdsnotacredito',
                                        'fdstreintaporciento',
                                        'fdsreconocer',
                                        'fdssaldo',
                                        'fdspedido'
                                    ]);

                if($fds){
                    
                    // $idFacturaEncontrada = $fds->fdsid;
                    $sumSfsvalorizado = sfssubsidiosfacturassi::where('fdsid', $fds->fdsid)
                                                ->where('fecid', $fecid)
                                                ->sum('sfsvalorizado');

                    $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                                    ->where('proid', $proid)
                                                                    ->where('ndsanulada', 0)
                                                                    ->sum('ndsvalorneto');
                    
                    $sumanotascredito = abs($sumanotascredito);

                    $editarSaldo = false;
                    if($fds->fdsnotacredito != $sumanotascredito){
                        $fds->fdsnotacredito = $sumanotascredito;
                        $editarSaldo = true;
                    }

                    $sumanotascredito = $sumanotascredito + $sumSfsvalorizado;
                    $editarSaldo = true;

                    if($editarSaldo == true){
                        if($fds->fdstreintaporciento >= $sumanotascredito){
                            $nuevoSaldo = $fds->fdstreintaporciento - $sumanotascredito;
    
                            $fds->fdsreconocer = $sumanotascredito;
    
                        }else{
    
                            $sumanotascredito = $sumanotascredito - $fds->fdstreintaporciento;
    
                            $fds->fdsreconocer = $fds->fdstreintaporciento;
    
                            $nuevoSaldo = 0;
                        }
                        $fds->fdssaldo = $nuevoSaldo;
                    }

                    if($sdemontoareconocerreal <=  $fds->fdssaldo){
                        $idFacturaEncontrada[] = $fds->fdsid;
                        $facturasAfectadas[] = array(
                            "id" => $fds->fdsid,
                            "valorizado" => $sdemontoareconocerreal,
                            "saldoAnterior" => $fds->fdssaldo,
                            "saldoNuevo" => $fds->fdssaldo - $sdemontoareconocerreal,
                            "objetivoActual" => $sdemontoareconocerreal,
                            "nuevoObjetivo" => 0
                        );

                        $fds->fdssaldo = $fds->fdssaldo - $sdemontoareconocerreal;
                        $fds->fdsreconocer = $fds->fdsreconocer + $sdemontoareconocerreal;
                        $fds->update();

                        $espendiente = false;

                    }else{
                        $idFacturaEncontrada[] = $fds->fdsid;
                        if($fds->fdssaldo > 0){
                            $facturasAfectadas[] = array(
                                "id" => $fds->fdsid,
                                "valorizado" => $fds->fdssaldo,
                                "saldoAnterior" => $fds->fdssaldo,
                                "saldoNuevo" => 0,
                                "objetivoActual" => $sdemontoareconocerreal,
                                "nuevoObjetivo" => $sdemontoareconocerreal - $fds->fdssaldo
                            );
                        }

                        $sdemontoareconocerreal = $sdemontoareconocerreal - $fds->fdssaldo;
                        $fds->fdsreconocer = $fds->fdsreconocer + $fds->fdssaldo;
                        $fds->fdssaldo = 0;
                        $fds->update();
                        // $idFacturaEncontrada = $this->BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses);
                        $dat = $this->BuscarFacturasSolic($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas, $sdecodigosolicitante, $cliclientegrupo);
                        $idFacturaEncontrada = $dat["idFacturaEncontrada"];
                        $espendiente = $dat["esPendiente"];
                        $facturasAfectadas = $dat["facturasAfectadas"];
    
                    }

                    // if($fds->update()){

                        // $fdse = fdsfacturassidetalles::find($fds->fdsid);

                        
                    // }
                    // break;
                }


            }
        }

        $dataObtenida = array(
            "idFacturaEncontrada" => $idFacturaEncontrada,
            "esPendiente" => $espendiente,
            "facturasAfectadas" => $facturasAfectadas
        );

        // return $idFacturaEncontrada;
        return $dataObtenida;

    }

    public function MetLogicaSubsidiosSiPendientes(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "NO_SE_ENCONTRARON_FACTURAS" => []
        );

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "";
        $datos          = [];
        $mensajeDetalle = "";

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

        $fecid = $request['fecid'];

        $fec = fecfechas::find($fecid);

        if($fec){
            $mesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 1 month")))->first();
            $dosMesesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 2 month")))->first();
            $tresMesesAnterior = fecfechas::where('fecfecha', date("Y-m-d", strtotime($fec->fecfecha."- 3 month")))->first();

            $mesAnteriorId = $mesAnterior->fecid;
            $dosMesesAnteriorId = $dosMesesAnterior->fecid;
            $tresMesesAnteriorId = $tresMesesAnterior->fecid;

            $meses = [$mesAnteriorId, $dosMesesAnteriorId, $tresMesesAnteriorId];

            // REINICIAR
            // $this->ActualizarReconocimientoSaldosFacturas($fecid);

            $sdes = sdesubsidiosdetalles::join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                        ->join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                        ->where('fecid', $fecid)
                                        ->where('sdeaprobado', true)
                                        ->where('sdemontoareconocerreal', '!=',0)
                                        ->where('sdependiente', true)
                                        ->get([
                                            'sdeid',
                                            'pro.proid',
                                            'pro.prosku',
                                            'fecid',
                                            'cli.cliid',
                                            'cli.clicodigo',
                                            'sdemontoareconocerreal',
                                            'sdemontoacido'
                                        ]);

            foreach($sdes as $sde){

                $idFacturaEncontrada = [];
                $facturasAfectadas = array();

                $dataObtenida = array(
                    "idFacturaEncontrada" => [],
                    "esPendiente" => false
                );

                $sumSfsvalorizadoSde = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)
                                        ->where('fecid', $fecid)
                                        ->sum('sfsvalorizado');

                $montoreconcoeracido = $sde->sdemontoacido - $sumSfsvalorizadoSde;

                $dataObtenida = $this->BuscarFacturasSubPendientes(
                    $idFacturaEncontrada, 
                    $sde->proid, 
                    $fecid, 
                    $sde->cliid, 
                    // $sde->sdemontoacido,
                    $montoreconcoeracido,
                    $meses, 
                    $facturasAfectadas
                );

                $idFacturaEncontrada = $dataObtenida["idFacturaEncontrada"];
                $facturasAfectadas   = $dataObtenida["facturasAfectadas"];

                if(sizeof($idFacturaEncontrada) > 0){

                    // $montoReconocerReal = $sde->sdemontoareconocerreal;
                    $montoReconocerReal = $sde->sdemontoacido;

                    foreach($facturasAfectadas as $facturaAfectada){
                        $fds = fdsfacturassidetalles::find($facturaAfectada['id']);

                        $sfsn = new sfssubsidiosfacturassi;
                        $sfsn->fecid = $fecid;
                        $sfsn->sdeid = $sde->sdeid;
                        $sfsn->fsiid = $fds->fsiid;
                        $sfsn->fdsid = $facturaAfectada['id'];
                        $sfsn->nsiid = null;
                        $sfsn->ndsid = null;
                        $sfsn->sfsvalorizado = $facturaAfectada['valorizado'];

                        $sfsn->sfssaldoanterior = $facturaAfectada['saldoAnterior'];
                        $sfsn->sfssaldonuevo    = $facturaAfectada['saldoNuevo'];

                        $sfsn->sfsobjetivo = $facturaAfectada['objetivoActual'];
                        $sfsn->sfsdiferenciaobjetivo = $facturaAfectada['nuevoObjetivo'];

                        $sfsn->save();
                    }

                    if($dataObtenida["esPendiente"] == true){
                        $logs["SUBSIDIOS_PENDIENTES"][] = "Subsidios pendientes al sde: ".$sde->sdeid;
                    }else{
                        $logs["SUBSIDIOS"][] = "Subsidios sde: ".$sde->sdeid;
                    }

                    // $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                    // $sdee->sdeencontrofactura = true;
                    // $sdee->sdependiente = $dataObtenida["esPendiente"];
                    // $sdee->update();

                }else{

                    // if($dataObtenida["esPendiente"] == true){
                    //     $logs["SUBSIDIOS_PENDIENTES"][] = "Subsidios pendientes al sde: ".$sde->sdeid;
                    // }else{
                    //     $logs["SUBSIDIOS"][] = "Subsidios sde: ".$sde->sdeid;
                    // }

                    // $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                    // $sdee->sdependiente = $dataObtenida["esPendiente"];
                    // $sdee->update();

                    $logs["NO_SE_ENCONTRARON_FACTURAS"][] = "No se encontro facturas para asignar al sde: ".$sde->sdeid;
                }

            }

        }else{
            return $fecid;
        }


        $logs["MENSAJE"] = $mensaje;

        $requestsalida = response()->json([
            "respuesta"      => $respuesta,
            "mensaje"        => $mensaje,
            "datos"          => $datos,
            "mensajeDetalle" => $mensajeDetalle,
            "logs" => $logs,
            "meses" => $meses
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken, // token
            $usu->usuid, // usuid
            null, // audip
            $request, // audjsonentrada
            $requestsalida,// audjsonsalida
            'EJECUTAR LOGICA PARA SELECCIONAR FACTURAS A LOS SUBSIDIOS SO EN EL MES: '.$fecid, //auddescripcion
            'EJECUTAR', // audaccion
            '/modulo/SubsidiosSi/logica', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;


    }

    public function BuscarFacturasSubPendientes($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas)
    {
        $espendiente = true;

        if($sdemontoareconocerreal < 0.1){

        }else{
            $espendiente = true;

            $fds = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                        ->where('fdsfacturassidetalles.proid', $proid)
                                        // ->where('fecid', $mes)
                                        ->where('fdsfacturassidetalles.cliid', $cliid)
                                        ->where(function ($query) use($idFacturaEncontrada) {
                                            foreach($idFacturaEncontrada as $id){
                                                $query->where('fdsid', '!=', $id);
                                            }
                                        })
                                        ->where('fdsanulada', 0)
                                        ->where('fdssaldo', '>', 1)
                                        ->where('fdssaldo', '!=', '0')
                                        ->where('fdssaldo', '!=', 0)
                                        ->where('fsiclase', '!=', "ZPF9")
                                        ->where('fsisunataprobado', true)
                                        ->first([
                                            'fdsfacturassidetalles.fdsid',
                                            'fdsnotacredito',
                                            'fdstreintaporciento',
                                            'fdsreconocer',
                                            'fdssaldo',
                                            'fdspedido'
                                        ]);

            if($fds){
                
                // $idFacturaEncontrada = $fds->fdsid;
                $sumSfsvalorizado = sfssubsidiosfacturassi::where('fdsid', $fds->fdsid)
                                            ->where('fecid', $fecid)
                                            ->sum('sfsvalorizado');

                $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                                ->where('proid', $proid)
                                                                ->where('ndsanulada', 0)
                                                                ->sum('ndsvalorneto');
                
                $sumanotascredito = abs($sumanotascredito);

                $editarSaldo = false;
                if($fds->fdsnotacredito != $sumanotascredito){
                    $fds->fdsnotacredito = $sumanotascredito;
                    $editarSaldo = true;
                }

                $sumanotascredito = $sumanotascredito + $sumSfsvalorizado;
                $editarSaldo = true;

                if($editarSaldo == true){
                    if($fds->fdstreintaporciento >= $sumanotascredito){
                        $nuevoSaldo = $fds->fdstreintaporciento - $sumanotascredito;

                        $fds->fdsreconocer = $sumanotascredito;

                    }else{

                        $sumanotascredito = $sumanotascredito - $fds->fdstreintaporciento;

                        $fds->fdsreconocer = $fds->fdstreintaporciento;

                        $nuevoSaldo = 0;
                    }
                    $fds->fdssaldo = $nuevoSaldo;
                }

                if($sdemontoareconocerreal <=  $fds->fdssaldo){
                    $idFacturaEncontrada[] = $fds->fdsid;
                    $facturasAfectadas[] = array(
                        "id" => $fds->fdsid,
                        "valorizado" => $sdemontoareconocerreal,
                        "saldoAnterior" => $fds->fdssaldo,
                        "saldoNuevo" => $fds->fdssaldo - $sdemontoareconocerreal,
                        "objetivoActual" => $sdemontoareconocerreal,
                        "nuevoObjetivo" => 0
                    );

                    $fds->fdssaldo = $fds->fdssaldo - $sdemontoareconocerreal;
                    $fds->fdsreconocer = $fds->fdsreconocer + $sdemontoareconocerreal;
                    $fds->update();

                    $espendiente = false;

                }else{
                    

                }

                // if($fds->update()){

                    // $fdse = fdsfacturassidetalles::find($fds->fdsid);

                    
                // }
                // break;
            }
        }


        $dataObtenida = array(
            "idFacturaEncontrada" => $idFacturaEncontrada,
            "esPendiente" => $espendiente,
            "facturasAfectadas" => $facturasAfectadas
        );

        // return $idFacturaEncontrada;
        return $dataObtenida;

    }

    public function ObtenerMesesAnteriores(Request $request)
    {
    }
}
