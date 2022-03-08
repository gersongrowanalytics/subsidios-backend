<?php

namespace App\Http\Controllers\Validaciones\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Validaciones\CustomMessagesController;
use App\Http\Controllers\Metodos\Login\MetLoginController;

class LoginController extends Controller
{
    public function ValLogin(Request $request)
    {

            $mensajes = new CustomMessagesController;
            $customMessages  = $mensajes->CustomMensajes();

            $rules = [
                'usuario' => ['required'],
                'contrasenia' => ['required'],
            ];

            $this->validate($request, $rules, $customMessages);

            $login = new MetLoginController;
            return $login->MetLogin($request);
        

        
    }

    public function ValCerrarSession(Request $request)
    {

        $login = new MetLoginController;
        return $login->MetCerrarSession($request);

    }
}
