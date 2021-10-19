<?php

namespace App\Http\Controllers\Metodos\Modulos\Facturas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fsifacturassi;
use App\Models\fdsfacturassidetalles;
use App\Models\sfssubsidiosfacturassi;
use App\Models\ndsnotascreditossidetalles;

class MetMostrarFacturasController extends Controller
{
    public function MetMostrarFacturas(Request $request)
    {

        $fechaInicio = $request['fechaInicio'];
        $fechaFinal  = $request['fechaFinal'];

        // if($fechaInicio != null){
        $fechaInicio = date("Y-m-d", strtotime($fechaInicio));
        $fechaFinal  = date("Y-m-d", strtotime($fechaFinal));
        // }

        $descargarFds = $this->ArmarExcelDescargaFacturasSi($fechaInicio, $fechaFinal);

        $fsis = fsifacturassi::join('fecfechas as fec', 'fec.fecid', 'fsifacturassi.fecid')
                            ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                // if($fechaInicio != null){
                                    $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                // }
                            })
                            ->limit(50)
                            ->get([
                                'fsiid',
                                'fsisolicitante',
                                'fsidestinatario',
                                'fsiclase',
                                'fsifecha',
                                'fsifactura',
                                'fsivalorneto',
                                'fsipedido',
                                'fsipedidooriginal'
                            ]);

        foreach($fsis as $posicionFsi => $fsi){
            $fdss = fdsfacturassidetalles::where('fdsfacturassidetalles.fsiid', $fsi->fsiid)
                                        ->get([
                                            'fdsid',
                                            'proid',
                                            'fdsmaterial',
                                            'fdsvalorneto',
                                            'fdssaldo',
                                            'fdsreconocer',
                                            'fdstreintaporciento',
                                            'fdsnotacredito'
                                        ]);
            
            foreach($fdss as $posicionFds => $fds){
                
                $sfss = sfssubsidiosfacturassi::where('fdsid', $fds->fdsid)->get();

                $fdss[$posicionFds]['sfs'] = $sfss;
            }

            $fsis[$posicionFsi]['fds'] = $fdss;
        }

        $requestsalida = response()->json([
            "datos" => $fsis,
            "descargarFds" => $descargarFds
        ]);

        return $requestsalida;

    }

    private function ArmarExcelDescargaFacturasSi($fechaInicio, $fechaFinal)
    {
        $nuevoArray = array(
            array(
                "columns" => [],
                "data"    => []
            )
        );

        // $ndss = ndsnotascreditossidetalles::join('fecfechas as fec', 'fec.fecid', 'ndsnotascreditossidetalles.fecid')
        //                                     ->where(function ($query) use($fechaInicio, $fechaFinal) {
        //                                         // if($fechaInicio != null){
        //                                             $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
        //                                         // }
        //                                     })
        //                                     ->get([
        //                                         'fec.fecanionumero',
        //                                         'fec.fecmesabreviacion',
        //                                         'fec.fecfecha',
        //                                         'ndsmaterial',
        //                                         'ndsclase',
        //                                         'ndsnotacredito',
        //                                         'ndsvalorneto',
        //                                         'ndspedido',
        //                                         'ndspedidooriginal'
        //                                     ]);

        $ndss = array();

        $fsis = fdsfacturassidetalles::join('fecfechas as fec', 'fec.fecid', 'fdsfacturassidetalles.fecid')
                            ->leftjoin('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                            ->where('fdsfacturassidetalles.cliid', 1504)
                            ->where('fdsfacturassidetalles.proid', 1)
                            // ->where(function ($query) use($fechaInicio, $fechaFinal) {
                            //     // if($fechaInicio != null){
                            //         $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                            //     // }
                            // })
                            // ->limit(50)
                            ->get([
                                'fec.fecanionumero',
                                'fec.fecmesabreviacion',
                                'fsi.fsiid',
                                'fsisolicitante',
                                'fsidestinatario',
                                'fdsmaterial',
                                'fsiclase',
                                'fsifecha',
                                'fsifactura',
                                'fdsvalorneto',
                                'fsipedido',
                                'fsipedidooriginal',
                                'fdspedido',
                                'fdsanulada'
                            ]);

        foreach($fsis as $posicionFsi => $fsi){

            if($posicionFsi == 0){

                $arrayTitulos = array(
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
                                    "rgb" => "FF1EBFED"
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "SOLIC.",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "DEST.",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "MATERIAL",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "ClFac",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "FECHA FAC.",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "FAC SELL IN",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "VALOR NETO",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "PEDIDO",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "PEDIDO ORIG.",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "NT",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "ANULADA",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                ); 

                $nuevoArray[0]['data'][] = $arrayFilaExcel;
            }            

            $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fds->fdspedido)
                                                            ->where('proid', 1)
                                                            ->where('ndsanulada', 0)
                                                            ->sum('ndsvalorneto'); // DATO EN NEGATIVO

            $sumanotascredito = abs($sumanotascredito); // VUELVE EL NÚMERO EN POSITIVO


            $arrayFilaExcel = array(
                array(
                    "value" => $fsi->fecanionumero,
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
                    "value" => $fsi->fecmesabreviacion,
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
                    "value" => $fsi->fsisolicitante,
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
                    // "value" => $fsi->fsidestinatario,
                    "value" => "130157",
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
                    "value" => $fsi->fdsmaterial,
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
                    "value" => $fsi->fsiclase,
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
                    "value" => $fsi->fsifecha,
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
                    "value" => $fsi->fsifactura,
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
                    "value" => floatval($fsi->fdsvalorneto),
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
                    "value" => $fsi->fsipedido,
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
                    "value" => $fsi->fsipedidooriginal,
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
                    "value" => floatval($sumanotascredito),
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
                    "value" => floatval($fsi->fdsanulada),
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
                
            );

            $nuevoArray[0]['data'][] = $arrayFilaExcel;

        }


        return $nuevoArray;
    }

    public function bk(){
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
                                            "cliclientesac"
                                        ]);

        foreach($descargarSdes as $posicionSde => $descargarSde){

            $sfss = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                            ->where('sdeid', $descargarSde->sdeid)
                                            ->get([
                                                'fsifactura',
                                                'sfsvalorizado'
                                            ]);

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
                );

                $nuevoArray[0]['columns'] = $arrayTitulos;

                // $arrayFilaExcel = array(
                //     array("value" => ""),
                //     array(
                //         "value" => "AÑO",
                //         "style" => array(
                //             "font" => array(
                //                 "sz" => "9",
                //                 "bold" => true,
                //                 "color" => array(
                //                     "rgb" => "FFFFFFFF"
                //                 )
                //             ),
                //             "fill" => array(
                //                 "patternType" => 'solid',
                //                 "fgColor" => array(
                //                     "rgb" => "FF000000"
                //                 )
                //             )
                            
                //         )
                //     ),
                //     array(
                //         "value" => $descargarSde->fecanionumero, 
                //         "style" => array(
                //             "font" => array(
                //                 "sz" => "9",
                //                 "bold" => true,
                //                 "color" => array(
                //                     "rgb" => "FFFFFFFF"
                //                 )
                //             ),
                //             "fill" => array(
                //                 "patternType" => 'solid',
                //                 "fgColor" => array(
                //                     "rgb" => "FF000000"
                //                 )
                //             )
                            
                //         )
                //     ),
                // );
                // $nuevoArray[0]['data'][] = $arrayFilaExcel;

                // $arrayFilaExcel = array(
                //     array("value" => ""),
                //     array(
                //         "value" => "MES",
                //         "style" => array(
                //             "font" => array(
                //                 "sz" => "9",
                //                 "bold" => true,
                //                 "color" => array(
                //                     "rgb" => "FFFFFFFF"
                //                 )
                //             ),
                //             "fill" => array(
                //                 "patternType" => 'solid',
                //                 "fgColor" => array(
                //                     "rgb" => "FF000000"
                //                 )
                //             )
                            
                //         )
                //     ),
                //     array(
                //         "value" => $descargarSde->fecmesabreviacion,
                //         "style" => array(
                //             "font" => array(
                //                 "sz" => "9",
                //                 "bold" => true,
                //                 "color" => array(
                //                     "rgb" => "FFFFFFFF"
                //                 )
                //             ),
                //             "fill" => array(
                //                 "patternType" => 'solid',
                //                 "fgColor" => array(
                //                     "rgb" => "FF000000"
                //                 )
                //             )
                            
                //         )
                //     ),
                // );
                // $nuevoArray[0]['data'][] = $arrayFilaExcel;

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
                        "value" => "CANTIDAD BULTOS SOFTYS",
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
                        "value" => "MONTO A REONOCER S/IGV",
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
                        "value" => "APROBADO",
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
                        "value" => "SAC",
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

                    // array(
                    //     "value" => "PK",
                    //     "style" => array(
                    //         "font" => array(
                    //             "sz" => "9",
                    //             "bold" => true,
                    //             "color" => array(
                    //                 "rgb" => "FFFFFFFF"
                    //             )
                    //         ),
                    //         "fill" => array(
                    //             "patternType" => 'solid',
                    //             "fgColor" => array(
                    //                 "rgb" => "FF222B35"
                    //             )
                    //         )
                            
                    //     )
                    // ),



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

                // array(
                //     "value" => floatval($rep->repimporte), 
                //     "style" => array(
                //         "font" => array(
                //             "sz" => "10"
                //         ),
                //         "numFmt" => "#,##0.00"
                //     )
                // ),
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
                    "value" => $descargarSde->sdeaprobado, 
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
                    "value" => $descargarSde->cliclientesac, 
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
    }

    private function ArmarExcelDescargaFacturasSibk($fechaInicio, $fechaFinal)
    {
        $nuevoArray = array(
            array(
                "columns" => [],
                "data"    => []
            )
        );

        $ndss = ndsnotascreditossidetalles::join('fecfechas as fec', 'fec.fecid', 'ndsnotascreditossidetalles.fecid')
                                            ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                                // if($fechaInicio != null){
                                                    $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                                // }
                                            })
                                            ->get([
                                                'fec.fecanionumero',
                                                'fec.fecmesabreviacion',
                                                'fec.fecfecha',
                                                'ndsmaterial',
                                                'ndsclase',
                                                'ndsnotacredito',
                                                'ndsvalorneto',
                                                'ndspedido',
                                                'ndspedidooriginal'
                                            ]);

        $fsis = fdsfacturassidetalles::join('fecfechas as fec', 'fec.fecid', 'fdsfacturassidetalles.fecid')
                            ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                            ->where(function ($query) use($fechaInicio, $fechaFinal) {
                                // if($fechaInicio != null){
                                    $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                // }
                            })
                            // ->limit(50)
                            ->get([
                                'fec.fecanionumero',
                                'fec.fecmesabreviacion',
                                'fsi.fsiid',
                                'fsisolicitante',
                                'fsidestinatario',
                                'fdsmaterial',
                                'fsiclase',
                                'fsifecha',
                                'fsifactura',
                                'fdsvalorneto',
                                'fsipedido',
                                'fsipedidooriginal'
                            ]);

        foreach($fsis as $posicionFsi => $fsi){

            if($posicionFsi == 0){

                $arrayTitulos = array(
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
                                    "rgb" => "FF1EBFED"
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "SOLIC.",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "DEST.",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "MATERIAL",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "ClFac",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "FECHA FAC.",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "FAC SELL IN",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "VALOR NETO",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "PEDIDO",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                    array(
                        "value" => "PEDIDO ORIG.",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),
                ); 

                $nuevoArray[0]['data'][] = $arrayFilaExcel;
            }            

            $arrayFilaExcel = array(
                array(
                    "value" => $fsi->fecanionumero,
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
                    "value" => $fsi->fecmesabreviacion,
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
                    "value" => $fsi->fsisolicitante,
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
                    "value" => $fsi->fsidestinatario,
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
                    "value" => $fsi->fdsmaterial,
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
                    "value" => $fsi->fsiclase,
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
                    "value" => $fsi->fsifecha,
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
                    "value" => $fsi->fsifactura,
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
                    "value" => floatval($fsi->fdsvalorneto),
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
                    "value" => $fsi->fsipedido,
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
                    "value" => $fsi->fsipedidooriginal,
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
                
            );

            $nuevoArray[0]['data'][] = $arrayFilaExcel;

        }


        foreach($ndss as $nds){

            $arrayFilaExcel = array(
                array(
                    "value" => $nds->fecanionumero,
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
                    "value" => $nds->fecmesabreviacion,
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
                    "value" => "-",
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
                    "value" => "-",
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
                    "value" => $nds->ndsmaterial,
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
                    "value" => $nds->ndsclase,
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
                    "value" => $nds->fecfecha,
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
                    "value" => $nds->ndsnotacredito,
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
                    "value" => floatval($nds->ndsvalorneto),
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
                    "value" => $nds->ndspedido,
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
                    "value" => $nds->ndspedidooriginal,
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
                
            );

            $nuevoArray[0]['data'][] = $arrayFilaExcel;


        }


        return $nuevoArray;
    }
}
