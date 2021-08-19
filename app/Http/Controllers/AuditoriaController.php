<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\audauditorias;
use App\Models\usuusuarios;

class AuditoriaController extends Controller
{
    public function registrarAuditoria(
        $usutoken,
        $usuid,
        $audip,
        $audjsonentrada,
        $audjsonsalida,
        $auddescripcion,
        $audaccion,
        $audruta,
        $audpk,
        $log
    )
    {

        $audjsonentrada = json_encode($audjsonentrada);
        $audjsonsalida = json_encode($audjsonsalida);
        $audpk = json_encode($audpk);

        $respuesta = false;

        if($usuid == null){
            $usuusuario = usuusuarios::where('usutoken', $usutoken)->first(['usuid']);
            if($usuusuario){
                $usuid = $usuusuario->usuid;
            }else{
                $usuid = null;
            }
        }

        $audultimo = audauditorias::orderby('audid', 'desc')->first();
        $pkid = $audultimo->audid + 1;

        $audauditorias = new audauditorias;
        $audauditorias->audid           = $pkid;
        $audauditorias->usuid           = $usuid;
        $audauditorias->audip           = $audip;
        if(strlen($audjsonentrada) < 100){
            $audauditorias->audjsonentrada   = $audjsonentrada;
        }else{
            $audauditorias->audjsonentrada   = substr($audjsonentrada, 0, 100);
        }

        if(strlen($audjsonsalida) < 100){
            $audauditorias->audjsonsalida   = $audjsonsalida;
        }else{
            $audauditorias->audjsonsalida   = substr($audjsonsalida, 0, 100);
        }

        $audauditorias->auddescripcion  = $auddescripcion;
        $audauditorias->audaccion       = $audaccion;
        $audauditorias->audruta         = $audruta;

        if(strlen($audpk) < 100){
            $audauditorias->audpk = $audpk;
        }else{
            $audauditorias->audpk = substr($audpk, 0, 100);
        }
        
        
        $log = json_encode($log);
        if(strlen($log) < 100){
            $audauditorias->audlog   = $log;
        }else{
            $audauditorias->audlog   = substr($log, 0, 100);
        }

        if($audauditorias->save()){
            $respuesta = true;
        }else{
            $respuesta = false;
        }

        return $respuesta;

    }
}
