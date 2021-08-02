<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosSi\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosSi\Mostrar\MetMostrarSubsidiosSiController;

class MostrarSubsidiosSiController extends Controller
{
    public function ValMostrarSubsidiosSi(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarSubsidiosSi = new MetMostrarSubsidiosSiController;
        return $MetMostrarSubsidiosSi->MetMostrarSubsidiosSi($request);

    }
}
