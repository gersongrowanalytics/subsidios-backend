<?php

namespace App\Http\Controllers\Metodos\Modulos\BigData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fsofacturasso;

class MetMostrarFacturasSoController extends Controller
{
    public function MetMostrarFacturasSo(Request $request)
    {

        $re_anio = $request['anio'];
        $re_mes  = $request['mes'];

        $fsos = fsofacturasso::join('fecfechas as fec', 'fec.fecid', 'fsofacturasso.fecid')
                                ->join('cliclientes as cli', 'cli.cliid', 'fsofacturasso.cliid')
                                ->join('proproductos as pro', 'pro.proid', 'fsofacturasso.proid')
                                ->where('fecmestexto', $re_mes)
                                ->where('fecanionumero', $re_anio)
                                ->get([
                                    'fec.fecanionumero',
                                    'fec.fecmesabreviacion',
                                    'clicodigoshipto',
                                    'prosku',
                                    'fsoruc',
                                    'fsocantidadbulto',
                                    'fsoventasinigv'
                                ]);

        $fsosDescargable = $this->ArmarExcelDescarga($fsos);

        $requestsalida = response()->json([
            "respuesta" => true,
            "data" => $fsos,
            "descargable" => $fsosDescargable,
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

        foreach($data as $posicionData => $dato){
            
            if($posicionData == 0){

                $arrayTitulos = array(
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
                    array("title" => "", "width" => array("wpx" => 100)),
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
                        "value" => "SKU",
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
                        "value" => "RUC",
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
                        "value" => "CANTIDAD BULTOS",
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
                        "value" => "VENTA S/IGV",
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
                    "value" => $dato->fecanionumero,
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
                    "value" => $dato->fecmesabreviacion,
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
                    "value" => $dato->clicodigoshipto,
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
                    "value" => $dato->prosku,
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
                    "value" => $dato->fsoruc,
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
                    "value" => floatval($dato->fsocantidadbulto),
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
                    "value" => floatval($dato->fsoventasinigv),
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

            $nuevoArray[0]['data'][] = $arrayFilaExcel;
        }

        return $nuevoArray;
    }
}
