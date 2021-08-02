<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarMaestraClientesController;

class CargarMaestraClientesController extends Controller
{
    public function CargarMaestraClientes(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $cargarMaestraClientes = new MetCargarMaestraClientesController;
        return $cargarMaestraClientes->CargarMaestraClientes($request);

    }
}
