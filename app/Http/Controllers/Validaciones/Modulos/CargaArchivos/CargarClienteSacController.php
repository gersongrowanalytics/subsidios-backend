<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarClienteSacController;

class CargarClienteSacController extends Controller
{
    public function ValCargarClienteSac(Request $request)
    {
        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetCargarClienteSac = new MetCargarClienteSacController;
        return $MetCargarClienteSac->MetCargarClienteSac($request);
    }
}
