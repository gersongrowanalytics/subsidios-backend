<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos\SO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\SO\MetCargarSOController;

class CargarSOController extends Controller
{
    public function ValCargarSO(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarSO = new MetCargarSOController;
        return $MetCargarSO->MetCargarSO($request);
    }
}
