<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarCostoXBultoController;

class CargarCostoXBultoController extends Controller
{
    public function ValCargarCostoXBulto(Request $request)
    {

        $MetCargarCostoXBulto = new MetCargarCostoXBultoController;
        return $MetCargarCostoXBulto->MetCargarCostoXBulto($request);

    }
}
