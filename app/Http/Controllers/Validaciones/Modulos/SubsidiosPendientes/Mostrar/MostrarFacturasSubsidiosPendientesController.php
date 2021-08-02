<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosPendientes\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes\Mostrar\MetMostrarFacturasSubsidiosPendientesController;

class MostrarFacturasSubsidiosPendientesController extends Controller
{
    public function ValMostrarFacturasSubsidiosPendientes(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarFacturasSubsidiosPendientes = new MetMostrarFacturasSubsidiosPendientesController;
        return $MetMostrarFacturasSubsidiosPendientes->MetMostrarFacturasSubsidiosPendientes($request);

    }
}
