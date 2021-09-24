<?php

namespace App\Http\Controllers\Validaciones\Modulos\Perfil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\Perfil\MetEditarPerfilController;

class EditarPerfilController extends Controller
{
    public function ValEditarPerfil(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetEditarPerfil = new MetEditarPerfilController;
        return $MetEditarPerfil->MetEditarPerfil($request);

    }

    public function ValEditarImagenPerfil(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $EditarImagenPerfil = new MetEditarPerfilController;
        return $EditarImagenPerfil->EditarImagenPerfil($request);

    }
}
