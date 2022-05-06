<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Editar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;
use App\Models\usuusuarios;
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
        $re_nuevacantidadbultosDT = $request['nuevacantidadbultosDT'];

        $sdee = sdesubsidiosdetalles::join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('sdesubsidiosdetalles.sdeid', $sdeid)
                                    ->first([
                                        'sdesubsidiosdetalles.sdeid',
                                        'fec.fecid',
                                        'fecmesabierto',
                                        'sdebultosacido',
                                        'sdemontoacido',
                                        'sdedsctodos',
                                        'sdebultosnoreconocido',
                                        'sdecantidadbultos',
                                        'sdecantidadbultosreal',
                                        'sdeaprobado'
                                    ]);
        
        if($sdee){

            // BULTOS DT
            $sdee->sdecantidadbultos = $re_nuevacantidadbultosDT;
            $sdee->sdemontoareconocer  = $re_nuevacantidadbultosDT * floatval($sdee->sdedsctodos);

            $sdee->sdebultosacido = $nuevacantidadbultos;
            $sdee->sdemontoacido  = $nuevacantidadbultos * floatval($sdee->sdedsctodos);

            if($sdee->fecmesabierto == true){
                $esRegularizacion = false;
                $sdebultosnoreconocido = $sdee->sdecantidadbultosreal - $nuevacantidadbultos;
                $sdee->sdependiente  = true;
            }else{
                $esRegularizacion = true;
                $sdebultosnoreconocido = $nuevacantidadbultos - $sdee->sdecantidadbultosreal;
            }

            $sdee->sdecantidadbultosdtmanual = $re_nuevacantidadbultosDT;

            $sdee->sdebultosnoreconocido = $sdebultosnoreconocido;
            $sdee->sderegularizacion     = $esRegularizacion;
            $sdee->sdeaprobado           = true;
            if($sdee->update()){

                // $this->LogicaSoXSubsidio($sdee->fecid, $sdee->sdeid);

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

    // ESTA ES LA LOGIA DE SO PARA UNA LINEA SUBSIDIOS, ESTE DEBE SER EL MISMO CODIGO QUE EL QUE ESTA UBICADO EN APP/HTTP/CONTROLLERS/METODOS/MODULOS/CARGAARCHIVOS/SO/MetCargarSOController/Alinear

    public function LogicaSoXSubsidio($fecid, $re_sdeid)
    {

        // $fecid = 1005;

        // $fso = fsofacturasso::where('fecid', $fecid)->get();

        sfosubsidiosfacturasso::where('fecid', $fecid)
                                ->where('sdeid', $re_sdeid)
                                ->delete();

        sdesubsidiosdetalles::where('fecid', $fecid)
                            ->where('sdeid', $re_sdeid)
                            ->where('sdesac', false)
                            ->update([
                                "sdecantidadbultosreal" => 0,
                                "sdemontoareconocerreal" => 0,
                                "sdebultosacido" => 0,
                                "sdemontoacido" => 0
                            ]);

        $sdes = sdesubsidiosdetalles::where('fecid', $fecid)
                                    ->where('sdeid', $re_sdeid)
                                    ->where('sdesac', false)
                                    ->get();

        foreach($sdes as $sde){
            $fso = fsofacturasso::join('proproductos as pro', 'pro.proid', 'fsofacturasso.proid')
                                ->where('fecid', $sde->fecid)
                                ->where('cliid', $sde->cliid)
                                // ->where('proid', $sde->proid)
                                ->where('prosku', $sde->sdecodigounitario)
                                ->where('fsoruc', $sde->sderucsubcliente)
                                ->first([
                                    'fsofacturasso.fsoid',
                                    'fsofacturasso.proid'
                                ]);

            if($fso){

                $fsos = fsofacturasso::where('fecid', $sde->fecid)
                                ->where('cliid', $sde->cliid)
                                ->where('proid', $fso->proid)
                                ->where('fsoruc', $sde->sderucsubcliente)
                                ->get();

                foreach($fsos as $fsoa){
                    $sfon = new sfosubsidiosfacturasso;
                    $sfon->fsoid = $fsoa->fsoid;
                    $sfon->sdeid = $sde->sdeid;
                    $sfon->fecid = $fecid;
                    $sfon->save();
                }

                $fsosuma = fsofacturasso::where('fecid', $sde->fecid)
                                ->where('cliid', $sde->cliid)
                                ->where('proid', $fso->proid)
                                ->where('fsoruc', $sde->sderucsubcliente)
                                ->sum('fsocantidadbulto');

                $montoAReconocerReal = 0;

                $pos = strpos($sde->sdebonificacion, "X");

                if($pos !== false){
                    
                    $montoAReconocerReal = floatval($sde->sdecantidadbultos);

                }else{

                    // MOSTRAR EN 0 SI LA CANTIDAD REAL ES NEGATIVA

                    if(is_numeric($sde->sdecantidadbultos)){
                        if($sde->sdecantidadbultos == 0){
    
                            $montoAReconocerReal = 0;
        
                        }else{
        
                            if(floatval($fsosuma) > floatval($sde->sdecantidadbultos)){
                                $montoAReconocerReal = floatval($sde->sdecantidadbultos);
                            }else{
                                $montoAReconocerReal = floatval($fsosuma);
                            }
        
                        }
                    }else{
                        $montoAReconocerReal = 0;
                    }
                }

                $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                $sdee->sdecantidadbultosreal = $montoAReconocerReal;
                $sdee->sdemontoareconocerreal = floatval($montoAReconocerReal) * floatval($sde->sdedsctodos);

                $status = "OK";
                if($montoAReconocerReal != $sde->sdecantidadbultos){
                    $status = "ERROR CANTIDADES";
                }

                $sdee->sdestatus = $status;
                $sdee->sdeaprobado = true;

                $sdee->update();
                
            }else{
                $status = "NO HAY SO";

                $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                $sdee->sdecantidadbultosreal = 0;
                $sdee->sdemontoareconocerreal = 0;
                $sdee->sdestatus = $status;
                $sdee->sdeaprobado = false;

                $sdee->sdebultosacido = 0;
                $sdee->sdemontoacido = 0;

                $sdee->update();
            }
        
        }


        $sdes = sdesubsidiosdetalles::where('fecid', $fecid)
                                    ->where('sdeid', $re_sdeid)
                                    ->get([
                                        'sdeid',
                                        'sdebultosnoreconocido',
                                        'sdemontoareconocerreal',
                                        'sdedsctodos',
                                        'sdecantidadbultosreal',
                                        'sdesumaregularizacion'
                                    ]);

        foreach($sdes as $sde){
            
            $bultoAcidos = $sde->sdecantidadbultosreal - ($sde->sdebultosnoreconocido);

            $sdee = sdesubsidiosdetalles::find($sde->sdeid);

            if($sde->sdesumaregularizacion == true){
                $bultoAcidos = $sde->sdecantidadbultosreal + abs($sde->sdebultosnoreconocido);
            }else{
                $bultoAcidos = $sde->sdecantidadbultosreal - $sde->sdebultosnoreconocido;
            }

            $sdee->sdebultosacido = $bultoAcidos;
            $sdee->sdemontoacido  = $bultoAcidos * floatval($sde->sdedsctodos);
            $sdee->update();
        }



        

    }
}
