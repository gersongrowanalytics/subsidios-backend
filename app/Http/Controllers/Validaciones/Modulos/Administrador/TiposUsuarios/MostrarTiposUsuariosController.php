<?php

namespace App\Http\Controllers\Validaciones\Modulos\Administrador\TiposUsuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\Administrador\TiposUsuarios\MetMostrarTiposUsuariosController;

class MostrarTiposUsuariosController extends Controller
{
    public function ValMostrarTiposUsuarios(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarTiposUsuarios = new MetMostrarTiposUsuariosController;
        return $MetMostrarTiposUsuarios->MetMostrarTiposUsuarios($request);

    }
}
