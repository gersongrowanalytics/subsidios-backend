<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosSo\Cargar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Cargar\MetCargarExcepcionesController;

class CargarExcepcionesController extends Controller
{
    public function ValCargarExcepciones(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarExcepciones = new MetCargarExcepcionesController;
        return $MetCargarExcepciones->MetCargarExcepciones($request);
        
    }
}
