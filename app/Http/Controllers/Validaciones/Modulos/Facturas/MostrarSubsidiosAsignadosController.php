<?php

namespace App\Http\Controllers\Validaciones\Modulos\Facturas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\Facturas\MetMostrarSubsidiosAsignadosController;

class MostrarSubsidiosAsignadosController extends Controller
{
    public function ValMostrarSubsidiosAsignados(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarSubsidiosAsignados = new MetMostrarSubsidiosAsignadosController;
        return $MetMostrarSubsidiosAsignados->MetMostrarSubsidiosAsignados($request);

    }
}
