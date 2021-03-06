<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sfssubsidiosfacturassi;
use App\Models\fdsfacturassidetalles;
use App\Models\sdesubsidiosdetalles;

class MetEliminarFacturasController extends Controller
{
    public function MetEliminarFacturas(Request $request)
    {

        $respuesta      = true;
        $mensaje        = "La factura se elimino correctamente";
        $datos          = [];
        $mensajeDetalle = "";
        $mensajedev = "";
        $logs = array(
            "FACTURA_ASIGNADA_NO_ENCONTRADA" => "",
            "DETALLE_FACTURA_ASIGNADA_NO_ENCONTRADA" => "",
            "DETALLE_FACTURA_ASIGNADA_NO_EDITADA" => "",
            "DETALLE_FACTURA_ASIGNADA_NO_ELIMINADA" => "",
        );

        $sfsid = $request['sfsid'];
        $fdsid = $request['fdsid'];


        try{

            $sfsd = sfssubsidiosfacturassi::find($sfsid);
            if($sfsd){

                $sfss = sfssubsidiosfacturassi::where('sdeid', $sfsd->sdeid)->get();
                
                $sdee = sdesubsidiosdetalles::where('sdeid', $sfsd->sdeid)->first();
                $objetivoSde = $sdee->sdemontoareconocerreal;

                foreach($sfss as $sfse){
                    
                    if($sfse->sfsid != $sfsid){
                        $sfsee = sfssubsidiosfacturassi::find($sfse->sfsid);
                        $sfsee->sfsdiferenciaobjetivo = $objetivoSde - $sfse->sfsvalorizado;
                        $sfsee->update();

                        $objetivoSde = $objetivoSde - $sfse->sfsvalorizado;
                    }
                }

                $fdse = fdsfacturassidetalles::find($fdsid);
                if($fdse){

                    $fdse->fdssaldo = $fdse->fdssaldo + $sfsd->sfsvalorizado;
                    $fdse->fdsreconocer = $fdse->fdsreconocer - $sfsd->sfsvalorizado;
                    if($fdse->update()){
                        if($sfsd->delete()){

                        }else{
                            $respuesta = false;
                            $mensaje = "Lo sentimos, no pudimos eliminar la factura, recomendamos actualizar la informaci??n";
                            $logs["DETALLE_FACTURA_ASIGNADA_NO_ELIMINADA"][] = "EL SFSID: ".$sfsid.", NO SE PUDO ELIMINAR LA FACTURA ASIGNADA";
                        }
                    }else{
                        $respuesta = false;
                        $mensaje = "Lo sentimos, no pudimos actualizar la informaci??n, recomendamos actualizar la pagina";
                        $logs["DETALLE_FACTURA_ASIGNADA_NO_EDITADA"][] = "EL FDSID: ".$fdsid.", NO SE PUDO ACTUALIZAR EL SALDO Y EL RECONOCIMIENTO";
                    }

                }else{
                    $respuesta = false;
                    $mensaje = "Lo sentimos, no encontramos el detalle de la factura, recomendamos actualizar la informaci??n";
                    $logs["DETALLE_FACTURA_ASIGNADA_NO_ENCONTRADA"][] = "EL FDSID: ".$fdsid;
                }
            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, no encontramos la factura asignada, recomendamos actualizar la informaci??n";
                $logs["FACTURA_ASIGNADA_NO_ENCONTRADA"][] = "EL SFSID: ".$sfsid;
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
