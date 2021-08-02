<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSi\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ndsnotascreditossidetalles;

class MetMostrarNotasCreditoFacturaController extends Controller
{
    public function MetMostrarNotasCreditoFactura(Request $request)
    {
        $pedidoOriginal = $request['pedidoOriginal'];
        $proid = $request['proid'];

        $ndss = ndsnotascreditossidetalles::join('fecfechas as fec', 'fec.fecid', 'ndsnotascreditossidetalles.fecid')
                                            ->where('ndspedidooriginal', $pedidoOriginal)
                                            ->where('proid', $proid)
                                            ->get([
                                                'ndsid',
                                                'ndsclase',
                                                'ndsnotacredito',
                                                'ndsvalorneto',
                                                'fecfecha'
                                            ]);

        $sumandss = ndsnotascreditossidetalles::where('ndspedidooriginal', $pedidoOriginal)
                                            ->where('proid', $proid)
                                            ->sum('ndsvalorneto');

        $requestsalida = response()->json([
            "datos" => $ndss,
            "total" => $sumandss
        ]);

        return $requestsalida;

    }
}
