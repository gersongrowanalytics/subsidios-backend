<?php

namespace App\Http\Controllers\Validaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomMessagesController extends Controller
{
    public function CustomMensajes()
    {
        return [
            'required' => 'El :attribute no puede estar vacio.',
            'max' => 'El :attribute no puede tener mas de :max caracteres',
            'between' => 'El :attribute debe tener entre :min a :max caracteres'
        ];
    }
}
