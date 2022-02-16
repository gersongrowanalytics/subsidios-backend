<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Editar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;
use App\Http\Controllers\AuditoriaController;

class MetEditarSubsidiosSoController extends Controller
{
    public function MetEditarBultosSubsidiosSo(Request $request)
    {

        // $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);
        $pkis = array();
        $logs = array();


        $respuesta = true;
        $mensaje = "La cantidad de bultos fue actualizada correctamente";

        $sdeid = $request['sdeid'];
        $nuevacantidadbultos = $request['nuevacantidadbultos'];

        $sdee = sdesubsidiosdetalles::join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('sdesubsidiosdetalles.sdeid', $sdeid)
                                    ->first([
                                        'sdesubsidiosdetalles.sdeid',
                                        'fecmesabierto',
                                        'sdebultosacido',
                                        'sdemontoacido',
                                        'sdedsctodos',
                                        'sdebultosnoreconocido',
                                        'sdecantidadbultosreal'
                                    ]);
        
        if($sdee){

            $sdee->sdebultosacido = $nuevacantidadbultos;
            $sdee->sdemontoacido  = $nuevacantidadbultos * floatval($sdee->sdedsctodos);

            if($sdee->fecmesabierto == true){
                $esRegularizacion = false;
                $sdebultosnoreconocido = $sdee->sdecantidadbultosreal - $nuevacantidadbultos;
            }else{
                $esRegularizacion = true;
                $sdebultosnoreconocido = $nuevacantidadbultos - $sdee->sdecantidadbultosreal;
            }

            $sdee->sdebultosnoreconocido = $sdebultosnoreconocido;
            $sdee->sderegularizacion     = $esRegularizacion;
            if($sdee->update()){

            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, no se pudo editar el subsidio, recomendamos actualizar la información";
            }

        }else{
            $respuesta = false;
            $mensaje = "Lo sentimos, el subsidio seleccionado no se encuentra, recomendamos actualizar la información";
        }

        $requestsalida = response()->json([
            "respuesta" => $respuesta,
            "mensaje" => $mensaje
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken, // token
            $usu->usuid, // usuid
            null, // audip
            $request, // audjsonentrada
            $requestsalida,// audjsonsalida
            'EDITAR REGULARIZACIÓN DE SUBSIDIOS ', //auddescripcion
            'EDITAR', // audaccion
            '/modulo/subsidiosSo/editar-bultos', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }
}
