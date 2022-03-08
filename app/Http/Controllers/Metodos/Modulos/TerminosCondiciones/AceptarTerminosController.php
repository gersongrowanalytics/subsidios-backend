<?php

namespace App\Http\Controllers\Metodos\Modulos\TerminosCondiciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\usuusuarios;
use App\Http\Controllers\AuditoriaController;

class AceptarTerminosController extends Controller
{
    public function AceptarTerminos(Request $request)
    {

        // $tpaidAud = 5; // Aceptar terminos y condiciones
        $usuidAud = 0;
        $pkid = array();
        $logs = array();

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $usu = usuusuarios::where('usutoken', $usutoken)->first();

        if($usu){
            $usuidAud = $usu->usuid;

            $descripcionAud = "El usuario: ".$usu->usuusaurio." acepto los terminos y condiciones";

            date_default_timezone_set("America/Lima");
            $fechaActual = date('Y-m-d H:i:s');

            $usu->usuaceptoterminos = $fechaActual;
            $usu->usucerrosesion = false;
            $usu->update();

            $respuesta = true;
            $mensaje = "Usted acepto los terminos y condiciones correctamente";

        }else{
            $descripcionAud = "Lo sentimos no se encontro el usuario al momento de querer aceptar los terminos y condiciones token enviado: ".$usutoken;
            $respuesta = false;
            $mensaje = "Lo sentimos, tuvimos problemas en encontrar tu usuario, porfavor actualiza la pagina";
        }


        $requestsalida = response()->json([
            'respuesta' => $respuesta,
            'mensaje'   => $mensaje
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken,
            $usuidAud,
            null,
            $request,
            $requestsalida,
            $descripcionAud,
            'ACEPTAR TERMINOS Y CONDICONES',
            '/aceptar-terminos-condiciones', //ruta
            $pkid,
            $logs
        );

        if($registrarAuditoria == true){

        }else{
            
        }
        
        return $requestsalida;

    }
}
