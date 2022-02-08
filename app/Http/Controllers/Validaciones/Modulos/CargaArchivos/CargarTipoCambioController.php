<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarTipoCambioController;

class CargarTipoCambioController extends Controller
{
    public function ValCargarTipoCambio(Request $request)
    {

        $MetCargarTipoCambio = new MetCargarTipoCambioController;
        return $MetCargarTipoCambio->MetCargarTipoCambio($request);

    }
}
