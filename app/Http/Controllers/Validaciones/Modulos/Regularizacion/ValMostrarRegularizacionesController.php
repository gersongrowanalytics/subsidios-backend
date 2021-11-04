<?php

namespace App\Http\Controllers\Validaciones\Modulos\Regularizacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\Regularizacion\MetMostrarRegularizacionesController;

class ValMostrarRegularizacionesController extends Controller
{
    public function ValMostrarRegularizaciones(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarRegularizaciones = new MetMostrarRegularizacionesController;
        return $MetMostrarRegularizaciones->MetMostrarRegularizaciones($request);

    }
}
