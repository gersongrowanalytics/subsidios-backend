<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosSi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosSi\MetLogicaSubsidiosSiController;

class LogicaSubsidiosSiController extends Controller
{
    public function ValLogicaSubsidiosSi(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetLogicaSubsidiosSi = new MetLogicaSubsidiosSiController;
        return $MetLogicaSubsidiosSi->MetLogicaSubsidiosSi($request);

    }

    public function ValLogicaSubsidiosSiSolic(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetLogicaSubsidiosSiSolic = new MetLogicaSubsidiosSiController;
        return $MetLogicaSubsidiosSiSolic->MetLogicaSubsidiosSiSolic($request);

    }

    
}
