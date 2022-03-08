<?php

namespace App\Http\Controllers\Metodos\Login;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\usuusuarios;
use App\Models\tuptiposusuariospermisos;
use App\Models\fecfechas;
use App\Models\sdesubsidiosdetalles;
use DateTime;

class MetLoginController extends Controller
{
    public function MetLogin(Request $request)
    {
        $mesespendientes = [];
        $subsidiospendientes = false;
        $tiempo = [];

        $estadoHttp = 200;
        $respuesta = true;
        $mensaje = "Bienvenido, ";
        $datos = [];
        $fechaDisponible = null;
        
        $usuario     = $request['usuario'];
        $contrasenia = $request['contrasenia'];

        $aparecerTerminosCondiciones = false;

        $usu = usuusuarios::join('perpersonas as per', 'per.perid', 'usuusuarios.perid')
                            ->join('tputiposusuarios as tpu', 'tpu.tpuid', 'usuusuarios.tpuid')
                            ->where('usuusuario', $usuario)
                            ->first([
                                'usuid',
                                'usuusuarios.estid',
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
                                'tpunombre',
                                'percumpleanios',
                                'pernumero',
                                'usuimagen',
                                'usuaceptoterminos',
                                'usucerrosesion'
                            ]);

        if($usu){
            if($usu->estid == 1){
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
    
                    $mesespendientes = [];
    
                    $sdes = sdesubsidiosdetalles::join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                                ->where('sdependiente', true)
                                                ->where('fec.fecid', '!=', 1104)
                                                ->distinct('sdesubsidiosdetalles.fecid')
                                                ->get([
                                                    'sdesubsidiosdetalles.fecid',
                                                    'fec.fecmesabreviacion',
                                                    'fec.fecanionumero',
                                                ]);
    
                    if(sizeof($sdes) > 0){
                        $subsidiospendientes = true;
                        foreach($sdes as $sde){
                            $mesespendientes[] = array(
                                "anio" => $sde->fecmesabreviacion,
                                "mes"  => $sde->fecanionumero
                            );
                        }
                        $tiempo = $sdes;
                    }
   
                    

                    if(isset($usu->usuaceptoterminos)){

                        date_default_timezone_set("America/Lima");
                        $fechaActual = new DateTime();
                        $fechaAceptacionTerminos = new DateTime($usu->usuaceptoterminos);

                        $diff = $fechaActual->diff($fechaAceptacionTerminos);

                        if($usu->usucerrosesion == true){
                            $aparecerTerminosCondiciones = true;
                        }else{
                            if($diff->days >= 7){
                                $aparecerTerminosCondiciones = true;

                                date_default_timezone_set("America/Lima");
                                $fechaActual = date('Y-m-d H:i:s');

                                $usue = usuusuarios::where('usuid', $usu->usuid)->first();
                                $usue->usuaceptoterminos = $fechaActual;
                                $usue->update();

                                $AuditoriaController = new AuditoriaController;
                                $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
                                    $usutoken,
                                    $usuidAud,
                                    null,
                                    $request,
                                    [],
                                    "VOLVER A ACEPTAR LOS TERMINOS  Y CONDICIONES DESPUES DE 7 DIAS",
                                    'ACEPTAR TERMINOS Y CONDICONES',
                                    '/aceptar-terminos-condiciones', //ruta
                                    [],
                                    [],
                                    5 // Aceptar terminos y condiciones
                                );

                            }else{
                                $aparecerTerminosCondiciones = false;
                            }
                        }


                    }else{
                        $aparecerTerminosCondiciones = true;
                    }



                }else{
                    $respuesta = false;
                    $mensaje = "Lo sentimos, el usuario o contraseña es incorrecta";
                }
            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, el usuario se encuentra actualmente desactivado";
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
            'subsidiospendientes' => $subsidiospendientes,
            'fechaActualizacion' => "22 Febrero 2022",
            'mesespendientes' => $mesespendientes,
            'tiempo' => $tiempo,
            'mostrarterminos' => $aparecerTerminosCondiciones,
        ]);
    }

    public function MetCerrarSession(Request $request)
    {

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $usu = usuusuarios::where('usutoken', $usutoken)->first();

        if($usu){
            $usu->usucerrosesion = true;
            $usu->update();
        }

    }
}
