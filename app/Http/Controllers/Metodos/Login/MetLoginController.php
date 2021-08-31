<?php

namespace App\Http\Controllers\Metodos\Login;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\usuusuarios;
use App\Models\tuptiposusuariospermisos;
use App\Models\fecfechas;

class MetLoginController extends Controller
{
    public function MetLogin(Request $request)
    {
        $estadoHttp = 200;
        $respuesta = true;
        $mensaje = "Bienvenido, ";
        $datos = [];
        $fechaDisponible = null;
        
        $usuario     = $request['usuario'];
        $contrasenia = $request['contrasenia'];

        $usu = usuusuarios::join('perpersonas as per', 'per.perid', 'usuusuarios.perid')
                            ->join('tputiposusuarios as tpu', 'tpu.tpuid', 'usuusuarios.tpuid')
                            ->where('usuusuario', $usuario)
                            ->first([
                                'usuid',
                                'per.perid',
                                'pernumerodocumentoidentidad',
                                'pernombrecompleto',
                                'pernombre',
                                'perapellidopaterno',
                                'perapellidomaterno',
                                'usucodigo',
                                'usuusuario',
                                'usucorreo',
                                'usutoken',
                                'usucontrasenia',
                                'tpu.tpuid',
                                'tpuprivilegio',
                                'tpunombre'
                            ]);

        if($usu){
            if (Hash::check($contrasenia, $usu->usucontrasenia)) {

                $fechaDisponible = fecfechas::where('fecmesabierto', true)->first();

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
            'fecha'     => $fechaDisponible,
            'subsidiospendientes' => false,
            'fechaActualizacion' => "30 Agosto 2021",
        ]);
    }
}
