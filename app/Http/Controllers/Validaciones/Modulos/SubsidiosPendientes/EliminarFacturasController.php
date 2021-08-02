<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosPendientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes\MetEliminarFacturasController;

class EliminarFacturasController extends Controller
{
    public function ValEliminarFacturas(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetEliminarFacturas = new MetEliminarFacturasController;
        return $MetEliminarFacturas->MetEliminarFacturas($request);

    }
}
