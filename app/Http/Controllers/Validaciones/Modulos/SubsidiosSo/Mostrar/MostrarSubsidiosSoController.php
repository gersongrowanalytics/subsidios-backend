<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosSo\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Mostrar\MetMostrarSubsidiosSoController;

class MostrarSubsidiosSoController extends Controller
{
    public function ValMostrarSubsidiosSo(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarSubsidiosSo = new MetMostrarSubsidiosSoController;
        return $MetMostrarSubsidiosSo->MetMostrarSubsidiosSo($request);
    }

    public function ValDescargableSubsidiosSo(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarSubsidiosSo = new MetMostrarSubsidiosSoController;
        return $MetMostrarSubsidiosSo->ArmarExcelDescargaSubsidiosSo($request);
    }
}
