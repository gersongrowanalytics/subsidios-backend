<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos\SO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\SO\MetCargarSubsidiosPlantillaController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\SO\MetCargarSubsidiosController;

class CargarSubsidiosController extends Controller
{
    public function ValCargarSubsidiosPlantilla(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarSubsidiosPlantilla = new MetCargarSubsidiosPlantillaController;
        return $MetCargarSubsidiosPlantilla->MetCargarSubsidiosPlantilla($request);

    }

    public function ValCargarSubsidios(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarSubsidios = new MetCargarSubsidiosController;
        return $MetCargarSubsidios->MetCargarSubsidios($request);

    }
}
