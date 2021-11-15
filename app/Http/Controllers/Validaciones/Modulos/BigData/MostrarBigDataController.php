<?php

namespace App\Http\Controllers\Validaciones\Modulos\BigData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\BigData\MetMostrarClientesController;
use App\Http\Controllers\Metodos\Modulos\BigData\MetMostrarFacturasSiController;
use App\Http\Controllers\Metodos\Modulos\BigData\MetMostrarFacturasSoController;
use App\Http\Controllers\Metodos\Modulos\BigData\MetMostrarMaterialesController;

class MostrarBigDataController extends Controller
{
    public function ValMostrarFacturasSi(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarFacturasSi = new MetMostrarFacturasSiController;
        return $MetMostrarFacturasSi->MetMostrarFacturasSi($request);

    }

    public function ValMostrarFacturasSo(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarFacturasSo = new MetMostrarFacturasSoController;
        return $MetMostrarFacturasSo->MetMostrarFacturasSo($request);

    }

    public function ValMostrarClientes(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarClientes = new MetMostrarClientesController;
        return $MetMostrarClientes->MetMostrarClientes($request);

    }

    public function ValMostrarMateriales(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarMateriales = new MetMostrarMaterialesController;
        return $MetMostrarMateriales->MetMostrarMateriales($request);

    }

}
