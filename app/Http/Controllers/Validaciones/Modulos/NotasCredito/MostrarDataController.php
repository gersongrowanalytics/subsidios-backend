<?php

namespace App\Http\Controllers\Validaciones\Modulos\NotasCredito;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Metodos\Modulos\NotasCredito\MetMostrarDataController;

class MostrarDataController extends Controller
{
    public function ValMostrarDataNc(Request $request)
    {
        
    }

    public function ValMostrarDataDistribuidoresNc(Request $request)
    {

        $MetMostrarDataDistribuidorasNc = new MetMostrarDataController;
        return $MetMostrarDataDistribuidorasNc->MetMostrarDataDistribuidorasNc($request);

    }
}
