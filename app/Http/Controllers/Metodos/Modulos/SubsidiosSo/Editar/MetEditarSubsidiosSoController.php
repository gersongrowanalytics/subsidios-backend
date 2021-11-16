<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Editar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;

class MetEditarSubsidiosSoController extends Controller
{
    public function MetEditarBultosSubsidiosSo(Request $request)
    {

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

        return $requestsalida;

    }
}
