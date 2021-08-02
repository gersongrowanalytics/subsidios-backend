<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosPendientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes\MetAsignarFacturasController;

class AsignarFacturasController extends Controller
{
    public function ValAsignarFacturas(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetAsignarFacturas = new MetAsignarFacturasController;
        return $MetAsignarFacturas->MetAsignarFacturas($request);

    }
}
