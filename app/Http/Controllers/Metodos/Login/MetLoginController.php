<?php

namespace App\Http\Controllers\Metodos\Login;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\usuusuarios;
use App\Models\tuptiposusuariospermisos;

class MetLoginController extends Controller
{
    public function MetLogin(Request $request)
    {
        $estadoHttp = 200;
        $respuesta = true;
        $mensaje = "Bienvenido, ";
        $datos = [];
        
        $usuario     = $request['usuario'];
        $contrasenia = $request['contrasenia'];

        $usu = usuusuarios::join('perpersonas as per', 'per.perid', 'usuusuarios.perid')
                            ->where('usuusuario', $usuario)
                            ->first();

        if($usu){
            if (Hash::check($contrasenia, $usu->usucontrasenia)) {

                $tuptiposusuariospermisos = tuptiposusuariospermisos::join('pempermisos as pem', 'pem.pemid', 'tuptiposusuariospermisos.pemid')
                                                                    ->where('tuptiposusuariospermisos.tpuid', $usu->tpuid )
                                                                    ->get([
                                                                        'tuptiposusuariospermisos.tupid',
                                                                        'pem.pemnombre',
                                                                        'pem.pemslug'
                                                                    ]);

                if(sizeof($tuptiposusuariospermisos) > 0){
                    $usu->permisos = $tuptiposusuariospermisos;
                }else{
                    $usu->permisos = [];
                }

                $mensaje = "Bienvenido, ".$usuario." es un gusto volver a verte por aquí";
                $datos = $usu;

            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, el usuario o contraseña es incorrecta";
            }
        }else{
            $respuesta = false;
            $mensaje = "Lo sentimos, el usuario o contraseña es incorrecta";
        }

        return response()->json([
            'respuesta' => $respuesta,
            'mensaje'   => $mensaje,
            'datos'     => $datos,
        ]);
    }
}
