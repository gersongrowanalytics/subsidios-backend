<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sfssubsidiosfacturassi;
use App\Models\fecfechas;
use App\Models\sdesubsidiosdetalles;
use App\Models\fdsfacturassidetalles;

class MetAsignarFacturasController extends Controller
{
    public function MetAsignarFacturas(Request $request)
    {

        $respuesta      = true;
        $mensaje        = "Las facturas se les asigno correctamente";
        $datos          = [];
        $mensajeDetalle = "";
        $mensajedev = "";

        $logs = array(
            "FECHA_NO_ENCONTRADA" => "",
            "SUBSIDIO_NO_ENCONTRADO" => "",
            "DETALLE_FACTURA_NO_ENCONTRADA" => [],
            "DETALLE_FACTURA_NO_EDITADA" => [],
            "REGISTRO_SUBSIDIO_NO_EDITADO" => []
        );

        $sdeid = $request['sdeid'];
        $sdemontoareconocerreal = $request['sdemontoareconocerreal'];
        $facturas = $request['facturas'];

        // date_default_timezone_set("America/Lima");
        // $fechaActual = date('Y-m');
        // $fechaActual = $fechaActual."-01";


        try{
            // $fec = fecfechas::where('fecfecha', $fechaActual)->first();

            // if($fec){

                

            // }else{
            //     $respuesta = false;
            //     $mensaje   = "No se encontro la fecha seleccionada";
            //     $logs["FECHA_NO_ENCONTRADA"] = "FECHA SELECCIONADA: ".$fechaActual;
            // }

            $sde = sdesubsidiosdetalles::find($sdeid);

            if($sde){

                foreach( $facturas as $factura ){
                
                    if(isset($factura['seleccionado'])){

                        if($factura['seleccionado'] == true){
                            $fdse = fdsfacturassidetalles::find($factura['fdsid']);

                            if($fdse){

                                $fdse->fdssaldo = $fdse->fdssaldo - $factura['impacto'];
                                $fdse->fdsreconocer = $fdse->fdsreconocer + $factura['impacto'];
                                if($fdse->update()){

                                    $sfsultimo = sfssubsidiosfacturassi::orderby('sfsid', 'desc')->first();
                                    $pksfsid = $sfsultimo->sfsid + 1;

                                    $sfsn = new sfssubsidiosfacturassi;
                                    $sfsn->sfsid = $pksfsid;
                                    $sfsn->fecid = $sde->fecid;
                                    $sfsn->sdeid = $sdeid;
                                    $sfsn->fsiid = $factura['fsiid'];
                                    $sfsn->fdsid = $factura['fdsid'];
                
                                    $sfsn->nsiid = null;
                                    $sfsn->ndsid = null;
                
                                    $sfsn->sfsvalorizado    = $factura['impacto'];
                                    $sfsn->sfssaldoanterior = $fdse->fdssaldo;
                                    $sfsn->sfssaldonuevo    = $fdse->fdssaldo - $factura['impacto'];
                                    $sfsn->sfsobjetivo      = $sdemontoareconocerreal;

                                    $sumsfs = sfssubsidiosfacturassi::where('sdeid', $sdeid)->sum('sfsvalorizado');
                                    $sumsfs = $sumsfs + $factura['impacto'];
                                    $sfsn->sfsdiferenciaobjetivo = $sdemontoareconocerreal - $sumsfs;


                                    if($sfsn->save()){
                                        
                                        $sdemontoareconocerreal = $sdemontoareconocerreal - $factura['impacto'];

                                    }else{
                                        $respuesta = false;
                                        $mensaje   = "No se pudo editar el registro del subsidio";
                                        $logs["REGISTRO_SUBSIDIO_NO_EDITADO"][] = "REGISTRO DEL SUBSIDIO NO EDITADO: ".$factura['fdsid'];
                                    }

                                }else{
                                    $respuesta = false;
                                    $mensaje   = "No se pudo editar la factura seleccionada";
                                    $logs["DETALLE_FACTURA_NO_EDITADA"][] = "FDSID NO EDITADO: ".$factura['fdsid'];
                                }

                            }else{
                                $respuesta = false;
                                $mensaje   = "No se encontro el detalle de la factura";
                                $logs["DETALLE_FACTURA_NO_ENCONTRADA"][] = "FDSID NO ENCONTRADA: ".$factura['fdsid'];
                            }   
                        }
                    }
                }

                $suma = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)->sum('sfsvalorizado');

                $sdee = sdesubsidiosdetalles::where('sdeid', $sde->sdeid)->first();
                $sdee->sumsfsvalorizado = $suma;
                $sdee->update();

            }else{
                $respuesta = false;
                $mensaje   = "No se encontro el subsidio seleccionado";
                $logs["SUBSIDIO_NO_ENCONTRADO"] = "SUBSIDIO SELECCIONADO SDEID: ".$sdeid;
            }

        } catch (Exception $e) {
            $mensajedev = $e->getMessage();
            $respuesta = false;
        }


        $rpta = array(
            "respuesta" => $respuesta,
            "mensaje" => $mensaje,
            "mensajedev" => $mensajedev,
            "logs" => $logs
        );

        return $rpta;
    }
}
