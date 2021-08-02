<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarMaestraProductosController;

class CargarMaestraProductosController extends Controller
{
    public function CargarMaestraProductos(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $cargarMaestraProductos = new MetCargarMaestraProductosController;
        return $cargarMaestraProductos->CargarMaestraProductos($request);

    }
}
