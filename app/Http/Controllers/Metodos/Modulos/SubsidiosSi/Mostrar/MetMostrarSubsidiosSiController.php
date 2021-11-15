<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSi\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;
use App\Models\sfssubsidiosfacturassi;
use App\Models\subsidiossiagrupado;
use App\Models\zonzonas;
use App\Models\cliclientes;
use DB;

class MetMostrarSubsidiosSiController extends Controller
{
    public function MetMostrarSubsidiosSi(Request $request)
    {

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        if($fechaInicio != null){
            $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        }

        // $descargarSde = $this->ArmarExcelDescargaSubsidiosSi($fechaInicio,$fechaFinal );
        $descargarSde = array();


        $zons = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        // if($fechaInicio != null){
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                            // $query->where('sdesubsidiosdetalles.fecid', 1104);
                                        // }
                                    })
                                    ->distinct('cli.clizona')
                                    // ->orderBy('clizonacodigo', 'DESC')
                                    // ->where('sdestatus', '!=', null)
                                    ->get([
                                        'cli.clizona'
                                    ]);

        $zonas = zonzonas::where(function ($query) use($zons) {
                            if(sizeof($zons) > 0){
                                foreach($zons as $zona){
                                    $query->orwhere('zonnombre', $zona->clizona);
                                }
                            }else{
                                $query->where('zonnombre', "-");
                            }
                        })
                        ->orderBy('zonorden', 'desc')
                        ->get([
                            'zonnombre as clizona'
                        ]);

        foreach($zonas as $posicionZon => $zon){

            $sdes = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                    ->join('catcategorias as cat', 'cat.catid', 'pro.catid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('clizona', $zon['clizona'])
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        // if($fechaInicio != null){
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                            // $query->where('sdesubsidiosdetalles.fecid', 1104);
                                        // }
                                    })
                                    // ->orderBy('sdestatus' , 'DESC')
                                    ->orderBy('sdeterritorio' , 'ASC')
                                    ->orderBy('clihml' , 'ASC')
                                    ->orderBy('clisuchml' , 'ASC')
                                    ->orderBy('sdesubcliente' , 'DESC')
                                    ->orderBy('sdesector' , 'DESC')
                                    ->orderBy('sdecantidadbultosreal' , 'DESC')
                                    ->get([
                                        'sdesubsidiosdetalles.sdeid',
                                        'cli.cliid',
                                        'clizona',
                                        'clisuchml',
                                        'clihml as clinombre',
                                        // 'clinombre',
                                        'sdesubcliente',
                                        'catnombre',
                                        'propresentacion',
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
                                        'fec.fecid',
                                        'fecfecha',
                                        // 'fsifecha as fecfecha',
                                        'sdependiente',
                                        'sdesac',
                                        'sdesector',
                                        'sdeterritorio',
                                        'sdevalidado',
                                        'clicodigoshipto',
                                        'sumsfsvalorizado',
                                        'sdebultosacido',
                                        'sdedsctodos',
                                        'sdemontoacido',
                                    ]);

            foreach($sdes as $posicionSde => $sde){

                if($sde->fecid <= 1103){ // MENOR A JULIO 2021

                    $sfss = array(
                        array(
                            "fsifactura"    => "F",
                            "fsipedido"     => "0",
                            "sfsvalorizado" => $sde->sdemontoareconocerreal,
                            "fecfecha"      => $sde->fecfecha,
                            "fdsreconocer"  => $sde->sdemontoareconocerreal,
                            "fdssaldo"      => "0",
                            "fdstreintaporciento" => $sde->sdemontoareconocerreal,
                            "fdsnotacredito"      => 0,
                            "fdsvalorneto"        => $sde->sdemontoareconocerreal,
                            "sfssaldoanterior"    => $sde->sdemontoareconocerreal,
                            "sfssaldonuevo"       => 0,
                        )
                    );

                    $sumsfsvalorizado = $sde->sdemontoareconocerreal;

                }else{
                    // $sfss = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                    //                         ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                    //                         ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                    //                         ->where('sdeid', $sde->sdeid)
                    //                         ->get([
                    //                             'fsifactura',
                    //                             'fsipedido',
                    //                             'sfsvalorizado',
                    //                             // 'fecfecha',
                    //                             'fdsreconocer',
                    //                             'fdssaldo',
                    //                             'fdstreintaporciento',
                    //                             'fdsnotacredito',
                    //                             'fdsvalorneto',
                    //                             'sfssaldoanterior',
                    //                             'sfssaldonuevo',
                    //                             'fsifecha as fecfecha',
                    //                         ]);

                    $sfss = array();

                    // $sumsfsvalorizado = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                    //                         ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                    //                         ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                    //                         ->where('sdeid', $sde->sdeid)
                    //                         ->sum('sfsvalorizado');
                }

                
                // $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                // $sdee->sumsfsvalorizado = $sumsfsvalorizado;
                // $sdee->update();


                $sdes[$posicionSde]['facturas'] = $sfss;


            }

            $zonas[$posicionZon]['data'] = $sdes;
        }
        

        
        
        $requestsalida = response()->json([
            "datos" => $zonas,
            "descargarSde" => $descargarSde,
            // "sumSde" => $sumSde
        ]);

        return $requestsalida;

    }

    public function ArmarExcelDescargaSubsidiosSiDestinatario (Request $request)
    {

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        if($fechaInicio != null){
            $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        }

        $nuevoArray = array(
            array(
                "columns" => [],
                "data"    => []
            )
        );

        $descargarSdes = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                        ->join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                        ->join('catcategorias as cat', 'cat.catid', 'pro.catid')
                                        ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            // if($fechaInicio != null){
                                                $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                                // $query->where('sdesubsidiosdetalles.fecid', 1104);
                                            // }
                                        })
                                        ->get([
                                            'sdesubsidiosdetalles.sdeid',
                                            'fecanionumero',
                                            'fecmesabreviacion',
                                            'clizona',
                                            'clinombre',
                                            "sdecodigosolicitante",
                                            "sdecodigodestinatario",
                                            "sdesegmentoscliente",
                                            "sdesubsegmentoscliente",
                                            "sderucsubcliente",
                                            "sdesubcliente",
                                            "sdenombrecomercial",
                                            "sdesector",
                                            "sdecodigounitario",
                                            "sdedescripcion",
                                            "sdepcsapfinal",
                                            "sdedscto",
                                            "sdepcsubsidiado",
                                            "sdemup",
                                            "sdepvpigv",
                                            "sdedsctodos",
                                            "sdedestrucsap",
                                            "sdeinicio",
                                            "sdebultosacordados",
                                            "sdecantidadbultos",
                                            "sdemontoareconocer",
                                            "sdecantidadbultosreal",
                                            "sdemontoareconocerreal",
                                            "sdestatus",
                                            "sdediferenciaahorro",
                                            "sdeaprobado",
                                            "prosku",
                                            "cliclientesac",
                                            "sdeterritorio",
                                            "sdevalidado"
                                        ]);

        foreach($descargarSdes as $posicionSde => $descargarSde){

            if($descargarSde->sdevalidado == "SIVALIDADOS"){
                $sfss = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                            ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                            ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                            ->where('sdeid', $descargarSde->sdeid)
                                            ->get([
                                                'fsi.fsifactura',
                                                'sfsvalorizado',
                                                'pronombre',
                                                'fdsmaterial'
                                            ]);

                $sfssSuma = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                            ->where('sdeid', $descargarSde->sdeid)
                                            ->sum('sfsvalorizado');
            }else{
                $sfss = array();
                $sfssSuma = 0;
            }

            if($posicionSde == 0){
                $arrayTitulos = array(
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                );

                $nuevoArray[0]['columns'] = $arrayTitulos;

                $arrayFilaExcel = array(
                    // array("value" => ""),
                    array(
                        "value" => "AÑO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "MES",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "ZONA",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "TERRITORIO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "CLIENTE",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "CODIGO SOLICITANTE",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "CODIGO DESTINATARIO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "SEGMENTO SOFTYS",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "SUB SEGMENTO SOFTYS",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "RUC SUB-CLIENTE",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "SUB-CLIENTE",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "NOMBRE COMERCIAL/GRUPO EMPRESARIAL",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "SECTOR",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "COD UNI",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "DESCRIPCIÓN",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "PC SAP FINAL",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "DSCTO %",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "PC SUBSIDIADO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "MUP",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "PVP $/IGV",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "DSCTO S/.",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "DEST+RUC+SAP",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "INICIO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "BULTOS ACORDADOS",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "CANTIDAD (BULTOS SOFTYS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "MONTO  (S/IGV SOFTYS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "STATUS DE SUBSIDIOS",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "TIPO DE DATA",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "TOTAL LIQUIDADO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "FALTA LIQUIDAR",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                );

                // for($i = 0; $i < 44; $i++){
                for($i = 0; $i < 258; $i++){
                    $pos = $i+1;

                    $arrayFilaExcel[] = array(
                        "value" => "FACTURA N°".$pos,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    );

                    $arrayFilaExcel[] = array(
                        "value" => "COD_MATERIAL N°".$pos,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    );

                    $arrayFilaExcel[] = array(
                        "value" => "MATERIAL N°".$pos,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    );

                    $arrayFilaExcel[] = array(
                        "value" => "VALORIZADO N°".$pos,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    );
                    
                }

                $nuevoArray[0]['data'][] = $arrayFilaExcel;
            }

            $arrayFilaExcel = array(
                array(
                    "value" => $descargarSde->fecanionumero,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->fecmesabreviacion,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                array(
                    "value" => $descargarSde->clizona,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdeterritorio, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->clinombre, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdecodigosolicitante, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdecodigodestinatario, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdesegmentoscliente, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdesubsegmentoscliente, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sderucsubcliente, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdesubcliente, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdenombrecomercial, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdesector, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdecodigounitario, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdedescripcion, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => floatval($descargarSde->sdepcsapfinal), 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    ),
                ),
                array(
                    "value" => floatval($descargarSde->sdedscto),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                array(
                    "value" => floatval($descargarSde->sdepcsubsidiado),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                array(
                    "value" => floatval($descargarSde->sdemup),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                array(
                    "value" => floatval($descargarSde->sdepvpigv),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                array(
                    "value" => floatval($descargarSde->sdedsctodos),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                array(
                    "value" => $descargarSde->sdedestrucsap, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->sdeinicio, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => floatval($descargarSde->sdebultosacordados),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                array(
                    "value" => floatval($descargarSde->sdecantidadbultosreal),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                array(
                    "value" => floatval($descargarSde->sdemontoareconocerreal),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                array(
                    // "value" => $descargarSde->sdeaprobado == 1 ?"Validados" :"No Validados", 
                    "value" => $descargarSde->sdevalidado == "SIVALIDADOS" ?"Subsidiado" :"No Subsidiado ", 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),
                array(
                    "value" => $descargarSde->cliclientesac == 1 ? "Manual" :"Automatico", 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                array(
                    "value" => floatval($sfssSuma),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),

                array(
                    "value" => floatval($descargarSde->sdemontoareconocerreal - $sfssSuma),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
            );

            foreach($sfss as $posicionSfs => $sfs){
                    
                $arrayFilaExcel[] = array(
                    "value" => $sfs->fsifactura,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                );

                $arrayFilaExcel[] = array(
                    "value" => $sfs->fdsmaterial,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                );

                $arrayFilaExcel[] = array(
                    "value" => $sfs->pronombre,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                );

                $arrayFilaExcel[] = array(
                    "value" => floatval($sfs->sfsvalorizado),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                );
            }

            $nuevoArray[0]['data'][] = $arrayFilaExcel;

        }

        $requestsalida = response()->json([
            "datos" => $nuevoArray,
        ]);

        return $requestsalida;

        // return $nuevoArray;
    }

    public function ArmarExcelDescargaSubsidiosSi(Request $request)
    {

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        if($fechaInicio != null){
            $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        }




        $nuevoArray = array(
            array(
                "columns" => [],
                "data"    => []
            )
        );

        $descargarSdes = sdesubsidiosdetalles::join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            // if($fechaInicio != null){
                                                $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                                // $query->where('sdesubsidiosdetalles.fecid', 1104);
                                            // }
                                        })
                                        ->select(
                                            "sdecodigodestinatario",
                                            "sdecodigounitario",
                                            "cliid",
                                            // "sdeterritorio",
                                            // "sdezona as clizona",
                                            // "sdeterritorio as sdeterritorio",
                                            // "sdecliente as clinombre",
                                            "fec.fecid",
                                            "fecanionumero",
                                            "fecmesabreviacion",
                                            DB::raw("SUM(sdebultosacordados) as sumaButlosAcordados"),
                                            DB::raw("SUM(sdecantidadbultos) as sumaCantidadBultos"),
                                            DB::raw("SUM(sdemontoareconocer) as sumaMontoReconocer"),
                                            DB::raw("SUM(sdecantidadbultosreal) as sumaCantidadBultosReal"),
                                            DB::raw("SUM(sdemontoareconocerreal) as sumaMontoReconocerReal"),
                                        )
                                        ->groupBy('sdecodigodestinatario')
                                        ->groupBy('sdecodigounitario')
                                        ->groupBy('cliid')
                                        ->groupBy('fecid')
                                        ->groupBy('fecanionumero')
                                        ->groupBy('fecmesabreviacion')
                                        ->get();

        $arrayCli = array();
        
        foreach($descargarSdes as $posicionSde => $descargarSde){

            $clienteSeleccionado = array();

            $encontroCli = false;

            if(sizeof($arrayCli) > 0){
                
                foreach($arrayCli as $arcli){
                    if($arcli['dest'] == $descargarSde->cliid){
                        $encontroCli = true;
                        $clienteSeleccionado = $arcli['cli'];
                    }
                }
            }

            if($encontroCli == false){
                $cli = cliclientes::where('cliid', $descargarSde->cliid)->first();
                $arrayCli[] = array(
                    "dest" => $descargarSde->cliid,
                    "cli"  => $cli
                );
                $clienteSeleccionado = $cli;
            }



            // $sfss = subsidiossiagrupado::where('sdecodigodestinatario', $descargarSde->sdecodigodestinatario)
            //                             ->where('sdecodigounitario', $descargarSde->sdecodigounitario)
            //                             ->where(function ($query) use($fechaInicio, $fechaFinal) {
            //                                 // if($fechaInicio != null){
            //                                     $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
            //                                     // $query->where('sdesubsidiosdetalles.fecid', 1104);
            //                                 // }
            //                             })
            //                             ->get([
            //                                 'fsifactura',
            //                                 'sfsvalorizado',
            //                                 'fdsmaterial',
            //                                 'pronombre'
            //                             ]);
            $sfss = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                        ->join('fecfechas as fec', 'fec.fecid', 'sde.fecid')
                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                        ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                        ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                        ->where('sdecodigodestinatario', $descargarSde->sdecodigodestinatario)
                                        ->where('sdecodigounitario', $descargarSde->sdecodigounitario)
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            // if($fechaInicio != null){
                                                $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                                // $query->where('sdesubsidiosdetalles.fecid', 1104);
                                            // }
                                        })
                                        ->groupBy('fsifactura')
                                        ->groupBy('fdsmaterial')
                                        ->groupBy('pronombre')
                                        ->select(
                                            "fsifactura",
                                            DB::raw("SUM(sfsvalorizado) as sfsvalorizado"),
                                            'fdsmaterial',
                                            'pronombre',
                                        )
                                        ->get([
                                            'fsi.fsifactura',
                                            'sfsvalorizado',
                                            'fdsmaterial',
                                            'pronombre'
                                        ]);
            // $sfssSuma = 0;
            $sfssSuma = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                        ->join('fecfechas as fec', 'fec.fecid', 'sde.fecid')
                                        ->where('sdecodigodestinatario', $descargarSde->sdecodigodestinatario)
                                        ->where('sdecodigounitario', $descargarSde->sdecodigounitario)
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            // if($fechaInicio != null){
                                                $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                                // $query->where('sdesubsidiosdetalles.fecid', 1104);
                                            // }
                                        })
                                        ->sum('sfsvalorizado');

            if($posicionSde == 0){
                $arrayTitulos = array(
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100))
                );

                $nuevoArray[0]['columns'] = $arrayTitulos;

                $arrayFilaExcel = array(
                    array(
                        "value" => "AÑO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "MES",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "ZONA",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "TERRITORIO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "CLIENTE",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "CODIGO DESTINATARIO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "CODIGO PRODUCTO",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),


                    array(
                        "value" => "BULTOS (ACORDADOS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "BULTOS (SOFTYS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "MONTO (S/IGV SOFTYS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "LIQUIDACIÓN (S/IGV SOFTYS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "LIQUIDACIÓN PENDIENTE",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    ),
                );

                for($i = 0; $i < 258; $i++){
                    $pos = $i+1;

                    $arrayFilaExcel[] = array(
                        "value" => "FACTURA N°".$pos,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    );

                    $arrayFilaExcel[] = array(
                        "value" => "COD_MATERIAL N°".$pos,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    );

                    $arrayFilaExcel[] = array(
                        "value" => "MATERIAL N°".$pos,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    );

                    $arrayFilaExcel[] = array(
                        "value" => "VALORIZADO N°".$pos,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FFFFFFFF"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FF004FB8"
                                )
                            )
                            
                        )
                    );
                    
                }

                $nuevoArray[0]['data'][] = $arrayFilaExcel;
            }

            $arrayFilaExcel = array(
                array(
                    "value" => $descargarSde->fecanionumero,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                array(
                    "value" => $descargarSde->fecmesabreviacion,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                array(
                    "value" => $clienteSeleccionado['clizona'],
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                array(
                    "value" => $clienteSeleccionado['clitv'], 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                array(
                    "value" => $clienteSeleccionado['clinombre'], 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                array(
                    "value" => $descargarSde->sdecodigodestinatario, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                array(
                    "value" => $descargarSde->sdecodigounitario, 
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                ),

                // 
                array(
                    "value" => floatval($descargarSde->sumaButlosAcordados),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),

                array(
                    "value" => floatval($descargarSde->sumaCantidadBultosReal),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),

                array(
                    "value" => floatval($descargarSde->sumaMontoReconocerReal),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),

                array(
                    "value" => floatval($sfssSuma),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),

                array(
                    "value" => floatval($descargarSde->sumaMontoReconocerReal - $sfssSuma),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                ),
                
            );

            foreach($sfss as $posicionSfs => $sfs){
                    
                $arrayFilaExcel[] = array(
                    "value" => $sfs->fsifactura,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                );

                $arrayFilaExcel[] = array(
                    "value" => $sfs->fdsmaterial,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                );

                $arrayFilaExcel[] = array(
                    "value" => $sfs->pronombre,
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        )
                    )
                );

                $arrayFilaExcel[] = array(
                    "value" => floatval(round($sfs->sfsvalorizado, 2)),
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF2F2F2"
                            )
                        ),
                        "numFmt" => "#,##0.00"
                    )
                );
            }

            $nuevoArray[0]['data'][] = $arrayFilaExcel;

        }

        // MOSTRAR LAS FACTURAS DE REGULARIZACION

        $descargarSdes = sdesubsidiosdetalles::join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecidregularizado')
                                        ->join('fecfechas as fecs', 'fecs.fecid', 'sdesubsidiosdetalles.fecid')
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            $query->whereBetween('fec.fecfecha', [$fechaInicio, $fechaFinal]);
                                        })
                                        ->select(
                                            "sdecodigodestinatario",
                                            "sdecodigounitario",
                                            "cliid",
                                            // "sdeterritorio",
                                            // "sdezona as clizona",
                                            // "sdeterritorio as sdeterritorio",
                                            // "sdecliente as clinombre",
                                            "fecs.fecid",
                                            "fecs.fecanionumero",
                                            "fecs.fecmesabreviacion",
                                            DB::raw("SUM(sdebultosacordados) as sumaButlosAcordados"),
                                            DB::raw("SUM(sdecantidadbultos) as sumaCantidadBultos"),
                                            DB::raw("SUM(sdemontoareconocer) as sumaMontoReconocer"),
                                            DB::raw("SUM(sdecantidadbultosreal) as sumaCantidadBultosReal"),
                                            DB::raw("SUM(sdemontoareconocerreal) as sumaMontoReconocerReal"),
                                        )
                                        ->groupBy('sdecodigodestinatario')
                                        ->groupBy('sdecodigounitario')
                                        ->groupBy('cliid')
                                        ->groupBy('fecs.fecid')
                                        ->groupBy('fecs.fecanionumero')
                                        ->groupBy('fecs.fecmesabreviacion')
                                        ->get();

            foreach($descargarSdes as $posicionSde => $descargarSde){

                $clienteSeleccionado = array();

                $encontroCli = false;

                if(sizeof($arrayCli) > 0){
                    
                    foreach($arrayCli as $arcli){
                        if($arcli['dest'] == $descargarSde->cliid){
                            $encontroCli = true;
                            $clienteSeleccionado = $arcli['cli'];
                        }
                    }
                }

                if($encontroCli == false){
                    $cli = cliclientes::where('cliid', $descargarSde->cliid)->first();
                    $arrayCli[] = array(
                        "dest" => $descargarSde->cliid,
                        "cli"  => $cli
                    );
                    $clienteSeleccionado = $cli;
                }

                $sfss = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                        ->join('fecfechas as fec', 'fec.fecid', 'sde.fecidregularizado')
                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                        ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                        ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                        ->where('sdecodigodestinatario', $descargarSde->sdecodigodestinatario)
                                        ->where('sdecodigounitario', $descargarSde->sdecodigounitario)
                                        ->where('sfsregularizado', true)
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                        })
                                        ->groupBy('fsifactura')
                                        ->groupBy('fdsmaterial')
                                        ->groupBy('pronombre')
                                        ->groupBy('sfsregularizado')
                                        ->select(
                                            "fsifactura",
                                            DB::raw("SUM(sfsvalorizado) as sfsvalorizado"),
                                            'fdsmaterial',
                                            'pronombre',
                                            'sfsregularizado'
                                        )
                                        ->get([
                                            'fsi.fsifactura',
                                            'sfsvalorizado',
                                            'fdsmaterial',
                                            'pronombre',
                                            'sfsregularizado'
                                        ]);

                $sfssSuma = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                        ->join('fecfechas as fec', 'fec.fecid', 'sde.fecidregularizado')
                                        ->where('sdecodigodestinatario', $descargarSde->sdecodigodestinatario)
                                        ->where('sdecodigounitario', $descargarSde->sdecodigounitario)
                                        ->where('sfsregularizado', true)
                                        ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                        })
                                        ->sum('sfsvalorizado');

                $arrayFilaExcel = array(
                    array(
                        "value" => $descargarSde->fecanionumero,
                        // "value" => "2021",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            )
                        )
                    ),

                    array(
                        "value" => $descargarSde->fecmesabreviacion,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            )
                        )
                    ),

                    array(
                        "value" => $clienteSeleccionado['clizona'],
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            )
                        )
                    ),

                    array(
                        "value" => $clienteSeleccionado['clitv'], 
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            )
                        )
                    ),

                    array(
                        "value" => $clienteSeleccionado['clinombre'], 
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            )
                        )
                    ),

                    array(
                        "value" => $descargarSde->sdecodigodestinatario, 
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            )
                        )
                    ),

                    array(
                        "value" => $descargarSde->sdecodigounitario, 
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            )
                        )
                    ),

                    // 
                    array(
                        "value" => floatval($descargarSde->sumaButlosAcordados),
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            ),
                            "numFmt" => "#,##0.00"
                        )
                    ),

                    array(
                        "value" => floatval($descargarSde->sumaCantidadBultosReal),
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            ),
                            "numFmt" => "#,##0.00"
                        )
                    ),

                    array(
                        "value" => floatval($descargarSde->sumaMontoReconocerReal),
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            ),
                            "numFmt" => "#,##0.00"
                        )
                    ),

                    array(
                        "value" => floatval($sfssSuma),
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            ),
                            "numFmt" => "#,##0.00"
                        )
                    ),

                    array(
                        "value" => floatval($descargarSde->sumaMontoReconocerReal - $sfssSuma),
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF2F2F2"
                                )
                            ),
                            "numFmt" => "#,##0.00"
                        )
                    ),
                    
                );

                foreach($sfss as $posicionSfs => $sfs){

                    $colorFondo = "FFF2F2F2";

                    if($sfs->sfsregularizado == true){
                        $colorFondo = "FFFFFF00";
                    }


                    $arrayFilaExcel[] = array(
                        "value" => $sfs->fsifactura,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => $colorFondo
                                )
                            )
                        )
                    );
    
                    $arrayFilaExcel[] = array(
                        "value" => $sfs->fdsmaterial,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => $colorFondo
                                )
                            )
                        )
                    );
    
                    $arrayFilaExcel[] = array(
                        "value" => $sfs->pronombre,
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => $colorFondo
                                )
                            )
                        )
                    );
    
                    $arrayFilaExcel[] = array(
                        "value" => floatval(round($sfs->sfsvalorizado, 2)),
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => $colorFondo
                                )
                            ),
                            "numFmt" => "#,##0.00"
                        )
                    );
                }

                $nuevoArray[0]['data'][] = $arrayFilaExcel;

            }

        // 






        $requestsalida = response()->json([
            "datos" => $nuevoArray,
            "data"  => $descargarSdes,
            // "sumas" => $sumaSde,
        ]);

        return $requestsalida;

        // return $nuevoArray;
    }

    public function MostrarFacturasAsignadas(Request $request)
    {

        $sdeid = $request['sdeid'];

        $sfss = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                ->join('fecfechas as fec', 'fec.fecid', 'fsi.fecid')
                                ->where('sdeid', $sdeid)
                                ->get([
                                    'fsifactura',
                                    'fsipedido',
                                    'sfsvalorizado',
                                    // 'fecfecha',
                                    'fdsreconocer',
                                    'fdssaldo',
                                    'fdstreintaporciento',
                                    'fdsnotacredito',
                                    'fdsvalorneto',
                                    'sfssaldoanterior',
                                    'sfssaldonuevo',
                                    'fsifecha as fecfecha',
                                ]);

        $requestsalida = response()->json([
            "datos" => $sfss,
        ]);

        return $requestsalida;


    }
}
