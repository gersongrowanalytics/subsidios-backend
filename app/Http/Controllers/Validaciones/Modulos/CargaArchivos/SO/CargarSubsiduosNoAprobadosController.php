<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos\SO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\SO\MetCargarSubsiduosNoAprobadosController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\SO\MetCargarSubsidiosSacController;

class CargarSubsiduosNoAprobadosController extends Controller
{
    public function ValCargarSubsiduosNoAprobados(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarSubsiduosNoAprobados = new MetCargarSubsiduosNoAprobadosController;
        return $MetCargarSubsiduosNoAprobados->MetCargarSubsiduosNoAprobados($request);
    }

    public function ValCargarSubsiduosSac(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarSubsidiosSac = new MetCargarSubsidiosSacController;
        return $MetCargarSubsidiosSac->MetCargarSubsidiosSac($request);
    }
}
