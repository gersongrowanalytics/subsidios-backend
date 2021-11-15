<?php

namespace App\Http\Controllers\Validaciones\Modulos\SubsidiosSo\Editar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Editar\MetEditarSubsidiosSoController;

class EditarSubsidiosSoController extends Controller
{
    public function ValEditarBultosSubsidiosSo(Request $request)
    {

        $mensajes = new CustomMessagesController;
        $customMessages  = $mensajes->CustomMensajes();

        $MetEditarSubsidiosSo = new MetEditarSubsidiosSoController;
        return $MetEditarSubsidiosSo->MetEditarBultosSubsidiosSo($request);

    }
}
