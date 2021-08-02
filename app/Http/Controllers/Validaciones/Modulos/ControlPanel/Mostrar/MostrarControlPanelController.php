<?php

namespace App\Http\Controllers\Validaciones\Modulos\ControlPanel\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\ControlPanel\Mostrar\MetMostrarControlPanelController;

class MostrarControlPanelController extends Controller
{
    public function ValMostrarControlPanel(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarControlPanel = new MetMostrarControlPanelController;
        return $MetMostrarControlPanel->MetMostrarControlPanel($request);
    }
}
