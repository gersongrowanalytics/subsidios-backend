<?php

namespace App\Http\Controllers\Validaciones\Modulos\Facturas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\Facturas\MetMostrarFacturasController;

class MostrarFacturasController extends Controller
{
    public function ValMostrarFacturas(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarFacturas = new MetMostrarFacturasController;
        return $MetMostrarFacturas->MetMostrarFacturas($request);

    }
}
