<?php

namespace App\Http\Controllers\Validaciones\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\CargaArchivos\MetCargarHistoricoDocumentosController;

class CargarHistoricoDocumentosController extends Controller
{
    public function CargarHistoricoDocumentos(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $cargarHistorico = new MetCargarHistoricoDocumentosController;
        return $cargarHistorico->CargarHistoricoDocumentos($request);

    }
}
