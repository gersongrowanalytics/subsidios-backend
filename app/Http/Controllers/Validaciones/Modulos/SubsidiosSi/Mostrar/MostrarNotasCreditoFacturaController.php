<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosSi\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosSi\Mostrar\MetMostrarNotasCreditoFacturaController;

class MostrarNotasCreditoFacturaController extends Controller
{
    public function ValMostrarNotasCreditoFactura(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarNotasCreditoFactura = new MetMostrarNotasCreditoFacturaController;
        return $MetMostrarNotasCreditoFactura->MetMostrarNotasCreditoFactura($request);

    }
}
