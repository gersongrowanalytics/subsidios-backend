<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;
use App\Models\zonzonas;
use App\Models\sfosubsidiosfacturasso;

class MetMostrarSubsidiosSoController extends Controller
{
    public function MetMostrarSubsidiosSo(Request $request)
    {

        $mostrarsolomatchs = $request['mostrarsolomatchs'];
        
        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        if($fechaInicio != null){
            $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
            $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        }else{

        }

        // $descargarSde = $this->ArmarExcelDescargaSubsidiosSo($fechaInicio, $fechaFinal);


        $zons = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                    })
                                    // ->where('sdestatus', '!=', null)
                                    ->distinct('cli.clizona')
                                    // ->orderBy('clizonacodigo', 'DESC')
                                    ->get([
                                        'cli.clizona',
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

        // foreach($zons){

        // }

        foreach($zonas as $posicionZon => $zon){

            $zonas[$posicionZon]['desplegado'] = false;
            
            $sdes = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                    ->join('catcategorias as cat', 'cat.catid', 'pro.catid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('clizona', $zon['clizona'])
                                    // ->where('sdestatus', '!=', null)
                                    ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                        // if($fechaInicio != null){
                                            $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                        // }
                                    })
                                    // ->orderBy('sdestatus' , 'DESC')
                                    ->orderBy('sdeterritorio' , 'ASC')
                                    ->orderBy('clihml' , 'ASC')
                                    ->orderBy('clisuchml' , 'ASC')
                                    ->orderBy('sdesubcliente' , 'DESC')
                                    ->orderBy('sdesector' , 'DESC')
                                    ->orderBy('sdecantidadbultos' , 'DESC')
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
                                        'prosku',
                                        'pronombre',
                                        'sdecantidadbultos',
                                        'sdemontoareconocer',
                                        'sdecantidadbultosreal',
                                        'sdemontoareconocerreal',
                                        'sdestatus',
                                        'sdediferenciaahorro',
                                        'sdebultosacordados',
                                        'sdesac',
                                        'sdesector',
                                        'clitv as sdeterritorio',
                                        'sdevalidado',
                                        'clicodigoshipto',
                                        'sdebultosacido',
                                        'sdedsctodos',
                                        'sdemontoacido'
                                    ]);

            $zonas[$posicionZon]['data'] = $sdes;
        }

        $requestsalida = response()->json([
            "datos" => $zonas,
            // "descargarSde" => $descargarSde,
            // "sumSde" => $sumSde
        ]);

        return $requestsalida;

    }

    public function ArmarExcelDescargaSubsidiosSo(Request $request)
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
                                            // }
                                        })
                                        ->orderby('cli.cliid')
                                        ->get([
                                            'sdeid',
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
                                            "clitv as sdeterritorio",
                                            "sdevalidado",
                                            "sdebultosnoreconocido",
                                            "sdebultosacido",
                                            "sdemontoacido"
                                        ]);

        foreach($descargarSdes as $posicionSde => $descargarSde){
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
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 200)),
                    array("title" => "", "width" => array("wpx" => 200)),
                    array("title" => "", "width" => array("wpx" => 200)),
                );

                $nuevoArray[0]['columns'] = $arrayTitulos;

                $arrayFilaExcel = array(
                    array(
                        "value" => "A??O",
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
                        "value" => "DESCRIPCI??N",
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
                        "value" => "BULTOS (DISTRIBUIDOR)",
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
                        "value" => "MONTO (S/IGV DT)",
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
                        "value" => "BULTOS NO RECONOCIDOS (TEMA DE FACTURAS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FF000000"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF79646"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "BULTOS ACIDOS (SOFTYS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FF000000"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF79646"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "MONTO ACIDO S/IGV (SOFTYS)",
                        "style" => array(
                            "font" => array(
                                "sz" => "9",
                                "bold" => true,
                                "color" => array(
                                    "rgb" => "FF000000"
                                )
                            ),
                            "fill" => array(
                                "patternType" => 'solid',
                                "fgColor" => array(
                                    "rgb" => "FFF79646"
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
                        "value" => "DIF. AHORRO (BULTOS)",
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
                        "value" => "DIF. AHORRO (SOLES)",
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

                for($i = 0; $i < 78; $i++){
                    $pos = $i+1;
                    $arrayFilaExcel[] = array(
                        "value" => "FACTURA N??".$pos,
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
                        "value" => "BULTOS N??".$pos,
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
                    )
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
                    "value" => floatval($descargarSde->sdecantidadbultos), 
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
                    "value" => floatval($descargarSde->sdemontoareconocer), 
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
                    "value" => floatval($descargarSde->sdebultosnoreconocido), 
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
                    "value" => floatval($descargarSde->sdebultosacido), 
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
                    "value" => floatval($descargarSde->sdemontoacido), 
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
                    "value" => $descargarSde->sdevalidado == "SIVALIDADOS" ?"Subsidiado" :"No Subsidiado", 
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
                    // "value" => floatval($descargarSde->sdecantidadbultos - $descargarSde->sdecantidadbultosreal), 
                    "value" => floatval($descargarSde->sdecantidadbultos - $descargarSde->sdebultosacido), 
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
                    // "value" => floatval($descargarSde->sdemontoareconocer - $descargarSde->sdemontoareconocerreal),
                    "value" => floatval($descargarSde->sdemontoareconocer - $descargarSde->sdemontoacido),
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
                // array(
                //     "value" => $descargarSde->sdecodigodestinatario.$descargarSde->prosku.$descargarSde->sderucsubcliente,
                //     "style" => array(
                //         "font" => array(
                //             "sz" => "9",
                //             "bold" => true,
                //         ),
                //         "fill" => array(
                //             "patternType" => 'solid',
                //             "fgColor" => array(
                //                 "rgb" => "FFF2F2F2"
                //             )
                //         )
                //     )
                // ),
            );

            
            $sfos = sfosubsidiosfacturasso::join('fsofacturasso as fso', 'fso.fsoid', 'sfosubsidiosfacturasso.fsoid')
                                            ->where('sfosubsidiosfacturasso.sdeid', $descargarSde->sdeid)
                                            ->get([
                                                'sfosubsidiosfacturasso.sfoid',
                                                'fsofactura',
                                                'fsocantidadbulto'
                                            ]);
                                            
            foreach($sfos as $sfo){

                $arrayFilaExcel[] = array(
                    "value" => $sfo->fsofactura,
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
                    "value" => floatval($sfo->fsocantidadbulto),
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

            $descargarSdes[$posicionSde]['sfos'] = $sfos;
        }

        return $requestsalida = response()->json([
            "datos" => $nuevoArray,
            "dataReal" => $descargarSdes
        ]);

    }

    public function VolverArmarExcel(Request $request)
    {

        $descargarSdes = $request['data'];
        $re_columnas  = $request['columnas'];

        $nuevoArray = array(
            array(
                "columns" => [],
                "data"    => []
            )
        );
        

        foreach($descargarSdes as $posicionSde => $descargarSde){
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
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 150)),
                    array("title" => "", "width" => array("wpx" => 200)),
                    array("title" => "", "width" => array("wpx" => 200)),
                    array("title" => "", "width" => array("wpx" => 200)),
                );

                $nuevoArray[0]['columns'] = $arrayTitulos;

                $arrayFilaExcel = array();

                foreach($re_columnas as $re_columna){


                    if($re_columna['columna'] == "A??o" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
                            "value" => "A??O",
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

                    if($re_columna['columna'] == "Mes" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Zona" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Territorio" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }

                    if($re_columna['columna'] == "Cliente" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Codigo Solicitante" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }

                    if($re_columna['columna'] == "Codigo Destinatario" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Segmento Softys" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }

                    if($re_columna['columna'] == "Sub Segmento Softys" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "RUC Sub Cliente" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }

                    if($re_columna['columna'] == "Sub Cliente" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Nombre Comercial/Grupo Empresarial" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Sector" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Cod Uni" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Descripci??n" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
                            "value" => "DESCRIPCI??N",
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
                    
                    if($re_columna['columna'] == "PC SAP Final" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Dsct %" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "PC Subsidiado" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );       
                    }
                    
                    if($re_columna['columna'] == "MUP" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "PVP $/IGV" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Dsct S/." && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "DEST + RUC + SAP" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }
                    
                    if($re_columna['columna'] == "Inicio" && $re_columna['seleccionado'] == true){
                        $arrayFilaExcel[] = array(
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
                        );
                    }

                }

                $arrayFilaExcel[] = array(
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
                );

                $arrayFilaExcel[] = array(
                    "value" => "BULTOS (DISTRIBUIDOR)",
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
                    "value" => "MONTO (S/IGV DT)",
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
                );

                $arrayFilaExcel[] = array(
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
                );

                $arrayFilaExcel[] = array(
                    "value" => "BULTOS NO RECONOCIDOS (TEMA DE FACTURAS)",
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                            "color" => array(
                                "rgb" => "FF000000"
                            )
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF79646"
                            )
                        )
                        
                    )
                );

                $arrayFilaExcel[] = array(
                    "value" => "BULTOS ACIDOS (SOFTYS)",
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                            "color" => array(
                                "rgb" => "FF000000"
                            )
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF79646"
                            )
                        )
                        
                    )
                );

                $arrayFilaExcel[] = array(
                    "value" => "MONTO ACIDO S/IGV (SOFTYS)",
                    "style" => array(
                        "font" => array(
                            "sz" => "9",
                            "bold" => true,
                            "color" => array(
                                "rgb" => "FF000000"
                            )
                        ),
                        "fill" => array(
                            "patternType" => 'solid',
                            "fgColor" => array(
                                "rgb" => "FFF79646"
                            )
                        )
                        
                    )
                );

                $arrayFilaExcel[] = array(
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
                );

                $arrayFilaExcel[] = array(
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
                );

                $arrayFilaExcel[] = array(
                    "value" => "DIF. AHORRO (BULTOS)",
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
                    "value" => "DIF. AHORRO (SOLES)",
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

                for($i = 0; $i < 78; $i++){
                    $pos = $i+1;
                    $arrayFilaExcel[] = array(
                        "value" => "FACTURA N??".$pos,
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
                        "value" => "BULTOS N??".$pos,
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

            $arrayFilaExcel = array();

            foreach($re_columnas as $re_columna){

                if($re_columna['columna'] == "A??o" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['fecanionumero'],
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
                }

                if($re_columna['columna'] == "Mes" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['fecmesabreviacion'],
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
                }

                if($re_columna['columna'] == "Zona" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['clizona'],
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
                }

                if($re_columna['columna'] == "Territorio" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdeterritorio'], 
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
                }

                if($re_columna['columna'] == "Cliente" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['clinombre'], 
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
                }

                if($re_columna['columna'] == "Codigo Solicitante" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdecodigosolicitante'], 
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
                }

                if($re_columna['columna'] == "Codigo Destinatario" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdecodigodestinatario'], 
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
                }

                if($re_columna['columna'] == "Segmento Softys" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdesegmentoscliente'], 
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
                }

                if($re_columna['columna'] == "Sub Segmento Softys" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdesubsegmentoscliente'], 
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
                }

                if($re_columna['columna'] == "RUC Sub Cliente" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sderucsubcliente'], 
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
                }

                if($re_columna['columna'] == "Sub Cliente" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdesubcliente'], 
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
                }

                if($re_columna['columna'] == "Nombre Comercial/Grupo Empresarial" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdenombrecomercial'], 
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
                }

                if($re_columna['columna'] == "Sector" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdesector'], 
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
                }

                if($re_columna['columna'] == "Cod Uni" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdecodigounitario'], 
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
                }

                if($re_columna['columna'] == "Descripci??n" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdedescripcion'], 
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
                }

                if($re_columna['columna'] == "PC SAP Final" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => floatval($descargarSde['sdepcsapfinal']), 
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

                if($re_columna['columna'] == "Dsct %" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => floatval($descargarSde['sdedscto']), 
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

                if($re_columna['columna'] == "PC Subsidiado" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => floatval($descargarSde['sdepcsubsidiado']), 
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

                if($re_columna['columna'] == "MUP" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => floatval($descargarSde['sdemup']), 
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

                if($re_columna['columna'] == "PVP $/IGV" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => floatval($descargarSde['sdepvpigv']), 
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

                if($re_columna['columna'] == "Dsct S/." && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => floatval($descargarSde['sdedsctodos']), 
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

                if($re_columna['columna'] == "DEST + RUC + SAP" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdedestrucsap'], 
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
                }

                if($re_columna['columna'] == "Inicio" && $re_columna['seleccionado'] == true){
                    $arrayFilaExcel[] = array(
                        "value" => $descargarSde['sdeinicio'], 
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
                }
                
                
            }

            $arrayFilaExcel[] = array(
                "value" => floatval($descargarSde['sdebultosacordados']), 
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

            $arrayFilaExcel[] = array(
                "value" => floatval($descargarSde['sdecantidadbultos']), 
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

            $arrayFilaExcel[] = array(
                "value" => floatval($descargarSde['sdemontoareconocer']), 
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

            $arrayFilaExcel[] = array(
                "value" => floatval($descargarSde['sdecantidadbultosreal']), 
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

            $arrayFilaExcel[] = array(
                "value" => floatval($descargarSde['sdemontoareconocerreal']), 
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

            $arrayFilaExcel[] = array(
                "value" => floatval($descargarSde['sdebultosnoreconocido']), 
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

            $arrayFilaExcel[] = array(
                "value" => floatval($descargarSde['sdebultosacido']), 
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

            $arrayFilaExcel[] = array(
                "value" => floatval($descargarSde['sdemontoacido']), 
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

            $arrayFilaExcel[] = array(
                // "value" => $descargarSde['sdeaprobado'] == 1 ?"Validados" :"No Validados", 
                "value" => $descargarSde['sdevalidado'] == "SIVALIDADOS" ?"Subsidiado" :"No Subsidiado", 
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
                "value" => $descargarSde['cliclientesac'] == 1 ? "Manual" :"Automatico", 
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
                // "value" => floatval($descargarSde['sdecantidadbultos'] - $descargarSde['sdecantidadbultosreal']), 
                "value" => floatval($descargarSde['sdecantidadbultos'] - $descargarSde['sdebultosacido']), 
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

            $arrayFilaExcel[] = array(
                // "value" => floatval($descargarSde['sdemontoareconocer'] - $descargarSde['sdemontoareconocerreal']),
                "value" => floatval($descargarSde['sdemontoareconocer'] - $descargarSde['sdemontoacido']),
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

            // $arrayFilaExcel = array(
                // array(
                //     "value" => $descargarSde['sdecodigodestinatario'].$descargarSde['prosku'].$descargarSde['sderucsubcliente'],
                //     "style" => array(
                //         "font" => array(
                //             "sz" => "9",
                //             "bold" => true,
                //         ),
                //         "fill" => array(
                //             "patternType" => 'solid',
                //             "fgColor" => array(
                //                 "rgb" => "FFF2F2F2"
                //             )
                //         )
                //     )
                // ),
            // );

            
            // $sfos = sfosubsidiosfacturasso::join('fsofacturasso as fso', 'fso.fsoid', 'sfosubsidiosfacturasso.fsoid')
            //                                 ->where('sfosubsidiosfacturasso.sdeid', $descargarSde['sdeid'])
            //                                 ->get([
            //                                     'sfosubsidiosfacturasso.sfoid',
            //                                     'fsofactura',
            //                                     'fsocantidadbulto'
            //                                 ]);

            $sfos = $descargarSdes[$posicionSde]['sfos'];
                                            
            foreach($sfos as $sfo){

                $arrayFilaExcel[] = array(
                    "value" => $sfo['fsofactura'],
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
                    "value" => floatval($sfo['fsocantidadbulto']),
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

        return $requestsalida = response()->json([
            "datos" => $nuevoArray
        ]);


    }

    public function ArmarCabecerasDescargable()
    {

    }
}
