<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos\SI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\SI\MetCargarEstadoSunatSiController;

class CargarEstadoSunatSiController extends Controller
{
    public function ValCargarEstadoSunatSi(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarEstadoSunatSi = new MetCargarEstadoSunatSiController;
        return $MetCargarEstadoSunatSi->MetCargarEstadoSunatSi($request);

    }
}
