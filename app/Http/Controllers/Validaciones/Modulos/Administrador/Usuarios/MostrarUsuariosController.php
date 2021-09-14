<?php

namespace App\Http\Controllers\Validaciones\Modulos\Administrador\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\Administrador\Usuarios\MetMostrarUsuariosController;

class MostrarUsuariosController extends Controller
{
    public function ValMostrarUsuarios(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarUsuarios = new MetMostrarUsuariosController;
        return $MetMostrarUsuarios->MetMostrarUsuarios($request);

    }
}
