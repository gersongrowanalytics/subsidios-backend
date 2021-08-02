<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\usuusuarios;
use App\Models\tuptiposusuariospermisos;

class Permisos
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ruta = explode(env('APP_URL'), $request->url());
        $ruta = $ruta[1];

        $usuusuario = usuusuarios::join('tputiposusuarios as tpu', 'tpu.tpuid', 'usuusuarios.tpuid')
                                ->where('usuusuarios.usutoken', $request->header('api_token'))
                                ->first(['usuusuarios.usuid', 'tpu.tpuid', 'tpu.tpuprivilegio']);
        if($usuusuario){
            if($usuusuario->tpuprivilegio == 'todo'){
                $response = $next($request);
            }else{
                $tuptiposusuariospermisos = tuptiposusuariospermisos::join('pempermisos as pem', 'pem.pemid', 'tuptiposusuariospermisos.pemid')
                                                                    ->where('tuptiposusuariospermisos.tpuid', $usuusuario->tpuid )
                                                                    ->where('pem.pemruta', $ruta)
                                                                    ->first([
                                                                        'pem.pemslug',
                                                                        'pem.pemruta'
                                                                    ]);
                
                if($tuptiposusuariospermisos){
                    $response = $next($request);
                }else{
                    $response = response('Unauthorized', 401);        
                }
            }
        }else{
            $response = response('Unauthorized', 401);
        }

        return $response;
    }
}
