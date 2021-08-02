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
        $clis = cliclientes::get([
            'cliid as id',
            'clinombre as nombre'
        ]);
        $pros = proproductos::get([
            'proid as id',
            'pronombre as nombre'
        ]);
        $cats = catcategorias::get([
            'catid as id',
            'catnombre as nombre'
        ]);

        $zonas = cliclientes::distinct('clizona')->get(['clizona as nombre']);
        $territorios = cliclientes::distinct('cliregion')->get(['cliregion as nombre']);
        

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
