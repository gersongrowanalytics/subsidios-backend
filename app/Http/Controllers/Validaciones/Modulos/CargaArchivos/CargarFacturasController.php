<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarFacturasController;

class CargarFacturasController extends Controller
{
    public function CargarFacturas(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $cargarFacturas = new MetCargarFacturasController;
        return $cargarFacturas->CargarFacturas($request);
    }
}
