<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sdesubsidiosdetalles;
use App\Models\zonzonas;
use DB;

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

        $descargarSde = $this->ArmarExcelDescargaSubsidiosSo($fechaInicio, $fechaFinal);


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
                                    ->groupBy('sdecodigodestinatario')
                                    ->groupBy('pro.proid')
                                    // ->groupBy('cliid')
                                    // ->groupBy('cat.catid')
                                    // ->groupBy('sdeid')
                                    ->select(
                                        "sdecodigodestinatario",
                                        // 'cli.cliid',                                        
                                        DB::raw("SUM(sdebultosacordados) as sdebultosacordados"),
                                        DB::raw("SUM(sdecantidadbultos) as sdecantidadbultos"),
                                        DB::raw("SUM(sdemontoareconocer) as sdemontoareconocer"),
                                        DB::raw("SUM(sdecantidadbultosreal) as sdecantidadbultosreal"),
                                        DB::raw("SUM(sdemontoareconocerreal) as sdemontoareconocerreal"),

                                        // 'clizona',
                                        // 'clisuchml',
                                        // 'clihml as clinombre',
                                        // 'clinombre',
                                        // 'sdesubcliente',
                                        'cat.catid',
                                        'catnombre',
                                        'pro.proid',
                                        'propresentacion',
                                        'prosku',
                                        'pronombre',
                                        // 'sdeid', 
                                        // 'sdestatus',
                                        // 'sdediferenciaahorro',
                                        // 'sdebultosacordados',
                                        // 'sdesac',
                                        // 'sdesector',
                                        // 'sdeterritorio',
                                        // 'sdevalidado'
                                        // 'clicodigoshipto'
                                    )
                                    ->get();

            


            $zonas[$posicionZon]['data'] = $sdes;
        }

        $requestsalida = response()->json([
            "datos" => $zonas,
            "descargarSde" => $descargarSde,
            // "sumSde" => $sumSde
        ]);

        return $requestsalida;

    }

    private function ArmarExcelDescargaSubsidiosSo($fechaInicio, $fechaFinal)
    {
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
                    "value" => floatval($descargarSde->sdecantidadbultos - $descargarSde->sdecantidadbultosreal), 
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
                    "value" => floatval($descargarSde->sdemontoareconocer - $descargarSde->sdemontoareconocerreal),
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

            $nuevoArray[0]['data'][] = $arrayFilaExcel;

        }

        return $nuevoArray;
    }
}
