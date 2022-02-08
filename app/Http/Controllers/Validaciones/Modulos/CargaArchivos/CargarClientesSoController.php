<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarClientesSoController;

class CargarClientesSoController extends Controller
{
    public function ValCargarClientesSo(Request $request)
    {

        $MetCargarClientesSo = new MetCargarClientesSoController;
        return $MetCargarClientesSo->MetCargarClientesSo($request);

    }
}
