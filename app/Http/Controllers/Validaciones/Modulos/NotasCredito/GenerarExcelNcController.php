<?php

namespace App\Http\Controllers\Validaciones\Modulos\NotasCredito;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Metodos\Modulos\NotasCredito\MetGenerarExcelNcController;

class GenerarExcelNcController extends Controller
{
    public function ValGenerarExcelNc(Request $request)
    {

        // $mensajes = new CustomMessagesController;
        // $customMessages  = $mensajes->CustomMensajes();

        $MetGenerarExcelNc = new MetGenerarExcelNcController;
        return $MetGenerarExcelNc->MetGenerarExcelNc($request);
        // return $MetGenerarExcelNc->MetExcelNorte($request);

    }
}
