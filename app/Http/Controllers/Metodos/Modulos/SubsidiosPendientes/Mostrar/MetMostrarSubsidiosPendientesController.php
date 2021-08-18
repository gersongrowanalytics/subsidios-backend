<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosPendientes\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;
use App\Models\sfssubsidiosfacturassi;

class MetMostrarSubsidiosPendientesController extends Controller
{
    public function MetMostrarSubsidiosPendientes(Request $request)
    {

        // $descargarSde = $this->ArmarExcelDescargaSubsidiosSi();
        $descargarSde = [];

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        if($fechaInicio != null){
            $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        }

        $sumSde = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('sdeaprobado', 1 )
                                    ->where('sdependiente', 1 )
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        // if($fechaInicio != null){
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                        // }
                                    })
                                    ->sum('sdemontoareconocerreal');

        $zonas = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('sdeaprobado', 1 )
                                    ->where('sdependiente', 1 )
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        // if($fechaInicio != null){
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                        // }
                                    })
                                    // ->orderBy('clizonacodigo', 'DESC')
                                    ->distinct('cli.clizona')
                                    ->get([
                                        'cli.clizona'
                                    ]);

        foreach($zonas as $posicionZon => $zon){

            $sumSdeZona = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                    ->join('catcategorias as cat', 'cat.catid', 'pro.catid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('clizona', $zon['clizona'])
                                    ->where('sdestatus', '!=', null)
                                    ->where('sdeaprobado', 1 )
                                    ->where('sdependiente', 1 )
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        // if($fechaInicio != null){
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                        // }
                                    })
                                    ->orderBy('sdestatus' , 'DESC')
                                    ->sum('sdemontoareconocerreal');

            $zonas[$posicionZon]['sumSdeZona'] = $sumSdeZona;

            $sdes = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                    ->join('catcategorias as cat', 'cat.catid', 'pro.catid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('clizona', $zon['clizona'])
                                    ->where('sdestatus', '!=', null)
                                    ->where('sdeaprobado', 1 )
                                    ->where('sdependiente', 1 )
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        // if($fechaInicio != null){
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                        // }
                                    })
                                    ->orderBy('sdestatus' , 'DESC')
                                    ->get([
                                        'sdesubsidiosdetalles.sdeid',
                                        'cli.cliid',
                                        'clizona',
                                        'clinombre',
                                        'sdesubcliente',
                                        'catnombre',
                                        'pro.proid',
                                        'prosku',
                                        'pronombre',
                                        'sdecantidadbultos',
                                        'sdemontoareconocer',
                                        'sdecantidadbultosreal',
                                        'sdemontoareconocerreal',
                                        'sdestatus',
                                        'sdediferenciaahorro',
                                        'sdebultosacordados',
                                        'fecfecha',
                                        'sdependiente',
                                        'sderucsubcliente',
                                        'sdesubsidiosdetalles.sdecodigodestinatario',
                                        'sdesector',
                                        'sdeterritorio'
                                    ]);

            foreach($sdes as $posicionSde => $sde){
                $sfss = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                            ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                            ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                            ->where('sdeid', $sde->sdeid)
                                            ->get([
                                                'fsifactura',
                                                'fsipedido',
                                                'sfsvalorizado',
                                                'fecfecha',
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
                                                'fds.fdsid'
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
            "datos" => $zonas,
            "descargarSde" => $descargarSde,
            "sumSde" => $sumSde
        ]);

        return $requestsalida;
        
    }
}
