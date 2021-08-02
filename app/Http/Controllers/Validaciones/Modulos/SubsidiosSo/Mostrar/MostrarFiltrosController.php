<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosSo\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Mostrar\MetMostrarFiltrosController;

class MostrarFiltrosController extends Controller
{
    public function ValMostrarFiltros(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetMostrarFiltros = new MetMostrarFiltrosController;
        return $MetMostrarFiltros->MetMostrarFiltros($request);

    }
}
