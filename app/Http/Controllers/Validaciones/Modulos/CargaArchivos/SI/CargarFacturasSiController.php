<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos\SI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\SI\MetCargarFacturasSiController;

class CargarFacturasSiController extends Controller
{
    public function ValCargarFacturasSi(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarFacturasSi = new MetCargarFacturasSiController;
        return $MetCargarFacturasSi->MetCargarFacturasSi($request);
    }
}
