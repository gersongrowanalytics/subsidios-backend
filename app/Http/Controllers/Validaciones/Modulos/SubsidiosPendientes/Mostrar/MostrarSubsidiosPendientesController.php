<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosPendientes\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes\Mostrar\MetMostrarSubsidiosPendientesController;

class MostrarSubsidiosPendientesController extends Controller
{
    public function ValMostrarSubsidiosPendientes(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarSubsidiosPendientes = new MetMostrarSubsidiosPendientesController;
        return $MetMostrarSubsidiosPendientes->MetMostrarSubsidiosPendientes($request);

    }
}
