<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos\SO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\SO\MetCargarBultosNoReconocidosController;

class CargarBultosNoReconocidosController extends Controller
{
    public function ValCargarBultosNoReconocidos(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarBultosNoReconocidos = new MetCargarBultosNoReconocidosController;
        return $MetCargarBultosNoReconocidos->MetCargarBultosNoReconocidos($request);
    }
}
