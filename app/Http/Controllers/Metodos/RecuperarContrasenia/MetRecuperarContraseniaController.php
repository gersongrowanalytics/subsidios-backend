<?php

namespace App\Http\Controllers\Metodos\RecuperarContrasenia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailRecuperarContrasenaOutlook;
use App\Models\usuusuarios;
use Illuminate\Support\Str;

class MetRecuperarContraseniaController extends Controller
{
    public function MetRecuperarContrasenia(Request $request)
    {

        $respuesta = true;
        $mensaje   = "";
        // $data = ['nombre' => 'Gerson Vilca Alvarez', "usuario" => "Gerson", "contrasena" => "1234", "correo" => "gerson@hotmail.com"];
        $correo = $request['correo'];

        $usu = usuusuarios::where('usucorreo', $correo)->first();

        if($usu){
            $nuevoToken    = Str::random(60);
            $usu->usutoken = $nuevoToken;
            $usu->update();

            $data = ['token' => $nuevoToken];
            Mail::to($correo)->send(new MailRecuperarContrasenaOutlook($data));

            $respuesta = true;
            $mensaje   = "El correo fue enviado satisfactoriamente";
        }else{
            $respuesta = false;
            $mensaje   = "Lo sentimos, el correo ingresado no esta registrado en el sistema";
        }
        
        return response()->json([
            'respuesta' => $respuesta,
            'mensaje'   => $mensaje,
            'correo'  => $correo
        ]);

    }

    // {
    //     $data = [
    //         'token' => "",
    //         'nombre' => 'Gerson Vilca Alvarez', "usuario" => "Gerson", "contrasena" => "1234", "correo" => "gerson@hotmail.com"
    //     ];

    //     return view('CorreoRecuperarContrasena')->with($data);
    // }
}
