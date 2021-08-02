<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarMaestraFechasController;

class CargarMaestraFechasController extends Controller
{
    public function CargarMaestraFechas(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $cargarMaestraFechas = new MetCargarMaestraFechasController;
        return $cargarMaestraFechas->CargarMaestraFechas($request);

    }
}
