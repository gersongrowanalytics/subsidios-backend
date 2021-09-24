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
            $this->ActualizarReconocimientoSaldosFacturas($fecid);

            $sdes = sdesubsidiosdetalles::where('fecid', $fecid)
                                        ->where('sdeaprobado', true)
                                        ->where('sdemontoareconocerreal', '!=',0)
                                        ->get();

            foreach($sdes as $sde){

                $idFacturaEncontrada = [];
                $facturasAfectadas = array();

                $dataObtenida = array(
                    "idFacturaEncontrada" => [],
                    "esPendiente" => false
                );

                $dataObtenida = $this->BuscarFacturas(
                    $idFacturaEncontrada, 
                    $sde->proid, 
                    $fecid, 
                    $sde->cliid, 
                    $sde->sdemontoareconocerreal, 
                    $meses, 
                    $facturasAfectadas,

                );

                $idFacturaEncontrada = $dataObtenida["idFacturaEncontrada"];
                $facturasAfectadas   = $dataObtenida["facturasAfectadas"];

                if(sizeof($idFacturaEncontrada) > 0){

                    $montoReconocerReal = $sde->sdemontoareconocerreal;

                    // foreach($idFacturaEncontrada as $idFactura){

                    //     $fds = fdsfacturassidetalles::find($idFactura);

                    //     $valorizado = 0;

                    //     if($montoReconocerReal <=  $fds->fdssaldo){
                    //         $valorizado = $montoReconocerReal;
                    //         $montoReconocerReal = 0;
                    //     }else{
                    //         $valorizado = $fds->fdssaldo;
                    //         $montoReconocerReal = $montoReconocerReal - $fds->fdssaldo;
                    //     }

                    //     $sfsn = new sfssubsidiosfacturassi;
                    //     $sfsn->fecid = $fecid;
                    //     $sfsn->sdeid = $sde->sdeid;
                    //     $sfsn->fsiid = $fds->fsiid;
                    //     $sfsn->fdsid = $idFactura;
                    //     $sfsn->nsiid = null;
                    //     $sfsn->ndsid = null;
                    //     $sfsn->sfsvalorizado = $valorizado;
                    //     $sfsn->save();
                    // }

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

        $fdss = fdsfacturassidetalles::all();

        foreach($fdss as $fds){
            $fdse = fdsfacturassidetalles::find($fds->fdsid);
            $fdse->fdsreconocer   = 0;
            $fdse->fdsnotacredito = 0;
            $fdse->fdssaldo     = $fdse->fdstreintaporciento;
            $fdse->update();
        }

        //REINICIAMOS TODO EL FDS, EL FDSRECONOCER PODEMOS OBTENERLO SUMANDO SFS, EL FDSNOTACREDITO DE LAS NOTAS DE CREDITO DE LA DATA SUBIDA

    }

    public function BuscarFacturas($idFacturaEncontrada, $proid, $fecid, $cliid, $sdemontoareconocerreal, $meses, $facturasAfectadas)
    {


        $espendiente = true;

        $fds = fdsfacturassidetalles::where('proid', $proid)
                                    ->where('fecid', $fecid)
                                    ->where('cliid', $cliid)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->first();

        if($fds){

            $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                            ->where('proid', $proid)
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

                $fds = fdsfacturassidetalles::where('proid', $proid)
                                    ->where('fecid', $mes)
                                    ->where('cliid', $cliid)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->first();

                if($fds){
                    
                    // $idFacturaEncontrada[] = $fds->fdsid;

                    $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                                    ->where('proid', $proid)
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

                $fds = fdsfacturassidetalles::where('proid', $proid)
                                    // ->where('fecid', $mes)
                                    ->where('cliid', $cliid)
                                    ->where(function ($query) use($idFacturaEncontrada) {
                                        foreach($idFacturaEncontrada as $id){
                                            $query->where('fdsid', '!=', $id);
                                        }
                                    })
                                    ->where('fdssaldo', '!=', '0')
                                    ->where('fdssaldo', '!=', 0)
                                    ->first();

                if($fds){
                    
                    // $idFacturaEncontrada = $fds->fdsid;

                    $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                                    ->where('proid', $proid)
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

    public function ObtenerMesesAnteriores(Request $request)
    {
    }
}
