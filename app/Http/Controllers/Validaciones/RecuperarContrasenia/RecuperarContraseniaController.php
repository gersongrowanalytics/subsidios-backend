<?php

namespace App\Http\Controllers\Validaciones\RecuperarContrasenia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Metodos\RecuperarContrasenia\MetRecuperarContraseniaController;

class RecuperarContraseniaController extends Controller
{
    public function ValRecuperarContrasenia(Request $request)
    {
        $MetRecuperarContrasenia = new MetRecuperarContraseniaController;
        return $MetRecuperarContrasenia->MetRecuperarContrasenia($request);
    }

    public function ValCambiarContrasenia(Request $request)
    {
        $MetCambiarContrasenia = new MetRecuperarContraseniaController;
        return $MetCambiarContrasenia->MetCambiarContrasenia($request);
    }
}
