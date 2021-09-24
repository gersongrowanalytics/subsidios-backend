<?php

namespace App\Http\Controllers\Metodos\Modulos\Perfil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use Illuminate\Support\Facades\Hash;
use App\Models\usuusuarios;
use App\Models\perpersonas;
use Illuminate\Support\Str;

class MetEditarPerfilController extends Controller
{
    public function MetEditarPerfil(Request $request)
    {
        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $respuesta = true;
        $mensaje   = "El perfil fue editado correctamente";
        $usuid     = 0;

        $logs = array(
        );
        $pkis = array();

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }
        $campoEditar    = $request['campoEditar']; // 1 -> contrasenia 2 -> cumpleaños 3 -> telefono
        $re_contrasenia = $request['contrasenia'];
        $re_contraseniaActual = $request['contraseniaActual'];
        $re_cumpleanios = $request['cumpleanios'];
        $re_telefono    = $request['telefono'];

        $usu = usuusuarios::where('usutoken', $usutoken)->first();

        if($usu){
            
            $usuid = $usu->usuid;

            if($campoEditar == 1){

                if (Hash::check($re_contraseniaActual, $usu->usucontrasenia)) {
                    $usu->usucontrasenia = Hash::make($re_contrasenia);
                    $usu->update();
                }else{
                    $respuesta = false;
                    $mensaje = "Lo sentimos la contraseña actual no es la correcta";
                }
            }

            if($campoEditar == 2 || $campoEditar == 3){
                $per = perpersonas::where('perid', $usu->perid)->first();
            
                if($per){
                    
                    if($campoEditar == 2){
                        $per->percumpleanios = $re_cumpleanios;
                    }
                    if($campoEditar == 3){
                        $per->pernumero = $re_telefono;
                    }

                    $per->update();

                }else{
                    $respuesta = false;
                    $mensaje = "Lo sentimos tu usuario tiene algunos problemas con el registro de persona, porfavor contacta con soporte o actualiza la pagina gracias";
                }
            }

        }else{
            $respuesta = false;
            $mensaje = "Lo sentimos tu usuario tiene algunos problemas con el registro de usuario, porfavor contacta con soporte o actualiza la pagina gracias";
        }


        $requestsalida = response()->json([
            "respuesta" => $respuesta,
            "mensaje"   => $mensaje,
            "logs"      => $logs
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken, // token
            $usuid, // usuid
            null, // audip
            $request, // audjsonentrada
            $requestsalida,// audjsonsalida
            'EDITAR PERFIL DEL USUARIO', //auddescripcion
            'EDITAR', // audaccion
            '/modulo/perfil/editar', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }

    public function EditarImagenPerfil(Request $request)
    {

        $mensaje = "La imagen fue actualizada correctamente";
        $respuesta = true;

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }
        $imagen   = $request['imagen'];
        $direccionImagen = "";

        $usu = usuusuarios::where('usutoken', $usutoken)
                            ->first();

        if($usu){

            list(, $imagenIcono)  = explode(',', $imagen);
            $nombreImagen = $usu->usuid." - ".Str::random(10);
            $fichero     = '/Sistema/Modulos/Perfil/Usuario/'.$nombreImagen.'.png';
            file_put_contents(base_path().'/public'.$fichero, base64_decode($imagenIcono));

            $usu->usuimagen = env('APP_URL').$fichero;
            $usu->update();

            $direccionImagen = env('APP_URL').$fichero;

        }else{
            $mensaje = "Lo sentimos, el usuario no se encuentra registrado en el sistema, porfavor vuelva a iniciar sesión";
            $respuesta = false;
        }


        return response()->json([
            'respuesta' => $respuesta,
            'mensaje'   => $mensaje,
            'imagen'    => $direccionImagen
        ]);

    }
}
