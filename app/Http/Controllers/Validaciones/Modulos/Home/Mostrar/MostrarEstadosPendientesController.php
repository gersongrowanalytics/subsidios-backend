<?php

namespace App\Http\Controllers\Validaciones\Modulos\Home\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\Home\Mostrar\MetMostrarEstadosPendientesController;

class MostrarEstadosPendientesController extends Controller
{
    public function ValMostrarEstadosPendientes(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarEstadosPendientes = new MetMostrarEstadosPendientesController;
        return $MetMostrarEstadosPendientes->MetMostrarEstadosPendientes($request);

    }
}
