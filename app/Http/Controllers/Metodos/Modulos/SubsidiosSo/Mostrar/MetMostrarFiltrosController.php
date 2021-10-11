<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\proproductos;
use App\Models\catcategorias;
use App\Models\cliclientes;

class MetMostrarFiltrosController extends Controller
{
    public function MetMostrarFiltros(Request $request)
    {
        $clis = array();
        $pros = array();
        $cats = array();

        $zonas = array();
        $territorios = array();
        

        $requestsalida = response()->json([
            "solicitantes" => $clis,
            "productos" => $pros,
            "categorias" => $cats,
            "territorios" => $territorios,
            "zonas" => $zonas
        ]);

        return $requestsalida;
    }
}
