<?php

namespace App\Http\Controllers\Metodos\Modulos\Regularizacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;
use App\Models\sfssubsidiosfacturassi;

class MetMostrarRegularizacionesController extends Controller
{
    public function MetMostrarRegularizaciones(Request $request)
    {

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        if($fechaInicio != null){
            $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        }

        $zonas = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('sderegularizacion', true)
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                    })
                                    ->distinct('cli.clizona')
                                    ->get([
                                        'cli.clizona'
                                    ]);

        foreach($zonas as $posicionZon => $zon){
            $sdes = sdesubsidiosdetalles::join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                    ->join('coscodigossectores as cos', 'cos.cosid', 'pro.cosid')
                                    ->where('sderegularizacion', true)
                                    ->where('clizona', $zon['clizona'])
                                    ->get([
                                        'sdesubsidiosdetalles.sdeid',
                                        'fecmesabreviacion',
                                        'fecanionumero',
                                        'clizona',
                                        'clitv',
                                        'clihml',
                                        'clisuchml',
                                        'clicodigoshipto',
                                        'sderucsubcliente',
                                        'coscodigo',
                                        'cosnombre',
                                        'prosku',
                                        'pronombre',
                                        'sdemontoareconocerreal',
                                        'sdemontoacido',
                                        'sumsfsvalorizado',
                                        'sdecodigodestinatario'
                                    ]);


            foreach($sdes as $posicionSde => $sde){
                $sfss = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                            ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                            ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                            ->join('coscodigossectores as cos', 'cos.cosid', 'pro.cosid')
                                            ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                            ->where('sdeid', $sde->sdeid)
                                            ->get([
                                                'fsifactura',
                                                'fsipedido',
                                                'sfsvalorizado',
                                                // 'fecfecha',
                                                'fsifecha as fecfecha',
                                                'fdsreconocer',
                                                'fdssaldo',
                                                'fdstreintaporciento',
                                                'fdsnotacredito',
                                                'fdsvalorneto',
                                                'sfssaldoanterior',
                                                'sfssaldonuevo',
                                                'sfsobjetivo',
                                                'sfsdiferenciaobjetivo',
                                                'sfssubsidiosfacturassi.sfsid',
                                                'fds.fdsid',
                                                'pro.pronombre',
                                                'fdsmaterial',
                                                'coscodigo',
                                                'cosnombre'
                                            ]);

                $sdes[$posicionSde]['facturas'] = $sfss;

                $sumsfsvalorizado = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                            ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                            ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                            ->where('sdeid', $sde->sdeid)
                                            ->sum('sfsvalorizado');

                $sdes[$posicionSde]['sumsfsvalorizado'] = $sumsfsvalorizado;
            }

            $zonas[$posicionZon]['data'] = $sdes;
        }

        
        $requestsalida = response()->json([
            "datos" => $zonas
        ]);

        return $requestsalida;

    }
}
