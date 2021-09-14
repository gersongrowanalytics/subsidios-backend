<?php

namespace App\Http\Controllers\Metodos\Modulos\Administrador\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\usuusuarios;

class MetMostrarUsuariosController extends Controller
{
    public function MetMostrarUsuarios(Request $request)
    {

        $usus = usuusuarios::join('tputiposusuarios as tpu', 'tpu.tpuid', 'usuusuarios.tpuid')
                            ->join('perpersonas as per', 'per.perid', 'usuusuarios.perid')
                            ->get([
                                'usu.usuid',
                                'tpunombre',
                                'pernombrecompleto',
                                'usuusuario',
                                'usucorreo',
                                'usu.created_at',
                                'usu.updated_at'
                            ]);

        $requestsalida = response()->json([
            "datos" => $usus
        ]);

        return $requestsalida;

    }
}
