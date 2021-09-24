<?php

namespace App\Http\Controllers\Validaciones\Modulos\Administrador\ControlArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\Administrador\ControlArchivos\MetMostrarControlArchivosController;

class MostrarControlArchivosController extends Controller
{
    public function ValMostrarControlArchivos(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarControlArchivos = new MetMostrarControlArchivosController;
        return $MetMostrarControlArchivos->MetMostrarControlArchivos($request);

    }
}
