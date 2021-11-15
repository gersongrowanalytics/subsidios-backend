<?php

namespace App\Http\Controllers\Metodos\Modulos\BigData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fdsfacturassidetalles;
use App\Models\ndsnotascreditossidetalles;

class MetMostrarFacturasSiController extends Controller
{
    public function MetMostrarFacturasSi(Request $request)
    {

        $re_anio = $request['anio'];
        $re_mes  = $request['mes'];

        $fdss = fdsfacturassidetalles::join('fecfechas as fec', 'fec.fecid', 'fdsfacturassidetalles.fecid')
                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                        ->where('fecmestexto', $re_mes)
                                        ->where('fecanionumero', $re_anio)
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

        $fdssDescargable = $this->ArmarExcelDescarga($fdss);
                    
        $requestsalida = response()->json([
            "respuesta" => true,
            "data" => [1,2,3],
            "descargable" => $fdssDescargable,
        ]);

        return $requestsalida;

    }

    private function ArmarExcelDescarga($data)
    {
        $nuevoArray = array(
            array(
                "columns" => [],
                "data"    => []
            )
        );

        $ndss = array();

        
        foreach($data as $posicionFsi => $fsi){

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

            $sumanotascredito = ndsnotascreditossidetalles::where('ndspedidooriginal', $fsi->fdspedido)
                                                            ->where('ndsmaterial', $fsi->fdsmaterial)
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
                    "value" => $fsi->fsidestinatario,
                    // "value" => "130157",
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
                    "value" => $fsi->fdsanulada,
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
