<?php

namespace App\Http\Controllers\Metodos\Modulos\Administrador\TiposUsuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\tputiposusuarios;

class MetMostrarTiposUsuariosController extends Controller
{
    public function MetMostrarTiposUsuarios(Request $request)
    {

        $tpus = tputiposusuarios::all();

        $requestsalida = response()->json([
            "datos" => $tpus
        ]);

        return $requestsalida;

    }
}
