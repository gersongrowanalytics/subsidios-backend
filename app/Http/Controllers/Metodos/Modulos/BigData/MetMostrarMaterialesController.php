<?php

namespace App\Http\Controllers\Metodos\Modulos\BigData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\proproductos;

class MetMostrarMaterialesController extends Controller
{
    public function MetMostrarMateriales(Request $request)
    {

        $pros = proproductos::join('catcategorias as cat', 'cat.catid', 'proproductos.catid')
                            ->join('marmarcas as mar', 'mar.marid', 'proproductos.marid')
                            ->join('coscodigossectores as cos', 'cos.cosid', 'proproductos.cosid')
                            ->join('concodigosnegocios as con', 'con.conid', 'proproductos.conid')
                            ->get([
                                'prosku',
                                'pronombre',
                                'concodigo',
                                'connombre',
                                'catcodigo',
                                'catnombre',
                                'coscodigo',
                                'cosnombre',
                                'prosegmentacion',
                                'propresentacion',
                                'marnombre',
                                'proconteo',
                                'proformato',
                                'protalla',
                                'propeso',
                                'promecanica',
                                'profactorconversionbultos',
                                'profactorconversioncajas',
                                'profactorconversionpaquetes',
                                'profactorconversionunidadminimaindivisible',
                                'profactorconversiontoneladas',
                                'profactorconversionmilesunidades',
                            ]);

        $prosDescargable = $this->ArmarExcelDescarga($pros);

        $requestsalida = response()->json([
            "respuesta"   => true,
            "data"        => $pros,
            "descargable" => $prosDescargable,
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
                    array(
                        "value" => "CODIGO PRODUCTO SOFTYS",
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
                        "value" => "DESCRIPCION PRODUCTO SOFTYS",
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
                        "value" => "COD NEGOCIO",
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
                        "value" => "NEGOCIO",
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
                        "value" => "COD CATEGORIA",
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
                        "value" => "CATEGORIA",
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
                        "value" => "COD SECTOR",
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
                                    "rgb" => "FF1EBFED"
                                )
                            )
                            
                        )
                    ),

                    array(
                        "value" => "SEGMENTACION",
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
                        "value" => "PRESENTACION",
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
                        "value" => "MARCA",
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
                        "value" => "CONTEO",
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
                        "value" => "FORMATO",
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
                        "value" => "TALLA",
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
                        "value" => "PESO",
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
                        "value" => "MECANICA",
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
                        "value" => "FACTOR DE CONVERSION A BULTOS",
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
                        "value" => "FACTOR DE CONVERSION A CAJAS",
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
                        "value" => "FACTOR DE CONVERSION A PAQUETES",
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
                        "value" => "FACTOR DE CONVERSION A UNIDAD MÍNIMA INDIVISIBLE",
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
                        "value" => "FACTOR DE CONVERSION A TONELADAS",
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
                        "value" => "FACTOR DE CONVERSIÓN A MILES DE UNIDADES",
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
                    "value" => $dato->pronombre,
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
                    "value" => $dato->concodigo,
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
                    "value" => $dato->connombre,
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
                    "value" => $dato->catcodigo,
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
                    "value" => $dato->catnombre,
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
                    "value" => $dato->coscodigo,
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
                    "value" => $dato->cosnombre,
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
                    "value" => $dato->prosegmentacion,
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
                    "value" => $dato->propresentacion,
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
                    "value" => $dato->marnombre,
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
                    "value" => $dato->proconteo,
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
                    "value" => $dato->proformato,
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
                    "value" => $dato->protalla,
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
                    "value" => $dato->propeso,
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
                    "value" => $dato->promecanica,
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
                    "value" => $dato->profactorconversionbultos,
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
                    "value" => $dato->profactorconversioncajas,
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
                    "value" => $dato->profactorconversionpaquetes,
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
                    "value" => $dato->profactorconversionunidadminimaindivisible,
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
                    "value" => $dato->profactorconversiontoneladas,
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
                    "value" => $dato->profactorconversionmilesunidades,
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
