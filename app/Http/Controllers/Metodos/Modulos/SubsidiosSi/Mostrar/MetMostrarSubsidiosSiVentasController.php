<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSi\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fecfechas;
use App\Models\tictipocambios;
use App\Models\sdesubsidiosdetalles;
use ZipArchive;
use \DateTime;

class MetMostrarSubsidiosSiVentasController extends Controller
{
    public function MetMostrarSubsidiosSiVentas(Request $request)
    {

        $re_fechainicio = $request['fechaInicio'];
        $re_fechafinal  = $request['fechaFinal'];

        if($re_fechainicio != null){
            $re_fechainicio = date("Y-m", strtotime($re_fechainicio));
            $re_fechainicio = $re_fechainicio."-01";
            // $re_fechafinal  = date("Y-m-d", strtotime($re_fechafinal));
        }

        $links = [];
        $data  = [];
        $descargarHistorico = true;

        $fecs = fecfechas::
                        // where('fecfecha', $re_fechainicio)
                        whereBetween('fecfecha', [$re_fechainicio, $re_fechafinal])
                        ->distinct('fecid')
                        ->where('fecid', '<=', '1107') // SOLO TENEMOS HISTORICO DESDE NOV
                        ->get([
                            'fecid',
                            'fecanionumero',
                            'fecmestexto'
                        ]);

        $arr_fechas = array();

        if(sizeof($fecs) > 0){

            foreach ($fecs as $key => $fec) {
                
                $encontroFecha = false;

                foreach ($arr_fechas as $key => $arr_fecha) {
                    if($arr_fecha['anio'] == $fec->fecanionumero && $arr_fecha['mes'] == $fec->fecmestexto ){
                        $encontroFecha = true;
                    }
                }

                if($encontroFecha == false){
                    if($fec->fecanionumero == "2021" && $fec->fecmestexto == "Diciembre"){
                        $links[] = "SubsidiosVentas/"."2021"."/Subsidios"."Noviembre".".xlsx";
                    }else if($fec->fecanionumero == "2022" && $fec->fecmestexto == "Enero"){
                        $links[] = "SubsidiosVentas/"."2021"."/Subsidios"."Noviembre".".xlsx";
                    }else{
                        $links[] = "SubsidiosVentas/".$fec->fecanionumero."/Subsidios".$fec->fecmestexto.".xlsx";
                    }


                    $arr_fechas[] = array(
                        "anio" => $fec->fecanionumero,
                        "mes"  => $fec->fecmestexto,
                    );
                }

            }

            if(sizeof($links) > 1){

                if( file_exists("SubsidiosVentas/Consolidados/".$re_fechainicio."-hasta-".$re_fechafinal."-comprimido.zip")  ){ //Destruye el archivo temporal
                    unlink("SubsidiosVentas/Consolidados/".$re_fechainicio."-hasta-".$re_fechafinal."-comprimido.zip");
                }
    
                $fileName="comprimido.rar";
                // Creamos un instancia de la clase ZipArchive
                $zip = new ZipArchive();
                // Creamos y abrimos un archivo zip temporal
                $zip->open("SubsidiosVentas/Consolidados/".$re_fechainicio."-hasta-".$re_fechafinal."-comprimido.zip", ZipArchive::CREATE);
                // Añadimos un directorio
                // $dir = 'miDirectorio';
                // $zip->addEmptyDir($dir);
    
                foreach ($links as $key => $link) {
                    
                    // $zip->addFile("SubsidiosVentas/Consolidados".$nombreArchivo);
                    $zip->addFile($link);
    
                    if($key+1 == sizeof($links)){
                        $zip->close();
                        // Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
                        // header("Content-type: application/octet-stream");
                        // header('Content-disposition: attachment; filename="'. urlencode($fileName).'"');
                        // leemos el archivo creado
                        // readfile('miarchivo.zip');
                        // Por último eliminamos el archivo temporal creado
                        // unlink('miarchivo.zip');//Destruye el archivo temporal
                    }
    
                }
    
                $ubicacion = "SubsidiosVentas/Consolidados/".$re_fechainicio."-hasta-".$re_fechafinal."-comprimido.zip";
                $links = [$ubicacion];
            }

        }else{

            $descargarHistorico = false;

            $nuevoArray = array(
                array(
                    "columns" => [],
                    "data"    => []
                )
            );

            // OBTENER TIPO DE CAMBIO

            $tic = tictipocambios::join('fecfechas as fec', 'fec.fecid', 'tictipocambios.fecid')
                                    ->where('fecfecha', $re_fechainicio)
                                    ->first();

            if($tic){
                $ticcambio = $tic->tictc;
            }else{
                $ticcambio = "0";
            }


            $descargarSdes = sdesubsidiosdetalles::leftjoin('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                                ->leftjoin('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                                ->leftjoin('csoclientesso as cso', 'cso.csoid', 'sdesubsidiosdetalles.csoid')
                                                ->leftjoin('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                                ->leftjoin('coscodigossectores as cos', 'cos.cosid', 'pro.cosid')
                                                ->leftjoin('cbucostosbultos as cbu', 'cbu.proid', 'pro.proid')
                                                ->leftjoin('fecfechas as feccbu', 'cbu.fecid', 'feccbu.fecid')
                                                ->where(function ($query) use($re_fechainicio) {
                                                    $query->where('fec.fecfecha', $re_fechainicio);
                                                    // $query->where('feccbu.fecfecha', $re_fechainicio);
                                                })
                                                ->get([
                                                    'sdeinicio',
                                                    'fec.fecanionumero',
                                                    'fec.fecmesabreviacion',
                                                    'fec.fecmesnumero',
                                                    'clizona',
                                                    'clitv',
                                                    'clihml',
                                                    'sdecodigosolicitante',
                                                    'sdecodigodestinatario',
                                                    'sderucsubcliente',
                                                    'cso.csosectorpbi',
                                                    'cso.csosegmento',
                                                    'cso.csosubsegmento',
                                                    'csosubcliente',
                                                    'coscodigo',
                                                    'cosnombre',
                                                    'sdecodigounitario',
                                                    'pronombre',
                                                    'profactorconversionpaquetes',
                                                    'prounidadeshojasxpaquete',
                                                    'prometrosxunidad',
                                                    'propeso',
                                                    'sdepcsapfinal',
                                                    'sdedestrucsap',
                                                    'sdemup',
                                                    'sdepvpigv',
                                                    'sdebultosacordados',
                                                    'sdepcsubsidiado',
                                                    'sdedscto',
                                                    'sdedsctodos',
                                                    'sdebultosacido',
                                                    'sdemontoacido',
                                                    'cbutotaldolares as cbutotal',
                                                    'csonombrecomercial'
                                                ]);

            if(sizeof($descargarSdes) > 0){

                foreach ($descargarSdes as $key => $descargarSde) {
                    
                    if($key == 0){
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
                                            "rgb" => "FF538DD5"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "DIAS VIGENCIA (360 d)",
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
                                            "rgb" => "FF538DD5"
                                        )
                                    )
                                    
                                )
                            ),
        

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
                                "value" => "ESCALA",
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
                                            "rgb" => "FFFF9900"
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
                                            "rgb" => "FFFF9900"
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
                                            "rgb" => "FFFF9900"
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
                                            "rgb" => "FFFF9900"
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
                                            "rgb" => "FFFF9900"
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
                                            "rgb" => "FF00B050"
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
                                            "rgb" => "FF00B050"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "NOMBRE COMERCIAL / GRUPO EMPRESARIAL",
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
                                            "rgb" => "FF00B050"
                                        )
                                    )
                                    
                                )
                            ),


                            array(
                                "value" => "SECTOR DE PBI",
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
                                            "rgb" => "FF00B050"
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
                                            "rgb" => "FF00B050"
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
                                            "rgb" => "FF00B050"
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
                                            "rgb" => "FF4BACC6"
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
                                            "rgb" => "FF4BACC6"
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
                                            "rgb" => "FF4BACC6"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "PAQUETES X BULTO",
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
                                            "rgb" => "FF4BACC6"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "UNIDADES / HOJAS X PAQUETE",
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
                                            "rgb" => "FF4BACC6"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "METROS X UNIDAD",
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
                                            "rgb" => "FF4BACC6"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "METROS / HOJAS / MILILITROS / PAÑOS",
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
                                            "rgb" => "FF4BACC6"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "PESO KG",
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
                                            "rgb" => "FF4BACC6"
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
                                            "rgb" => "FF0070C0"
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
                                            "rgb" => "FF8064A2"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "CANTIDAD EN TONS",
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
                                            "rgb" => "FF8064A2"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "PVP POR METRO",
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
                                            "rgb" => "FF8064A2"
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
                                            "rgb" => "FF8064A2"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "PVP S/IGV",
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
                                            "rgb" => "FF8064A2"
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
                                            "rgb" => "FF8064A2"
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
                                            "rgb" => "FF8064A2"
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
                                            "rgb" => "FF9BBB59"
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
                                            "rgb" => "FF9BBB59"
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
                                            "rgb" => "FF9BBB59"
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
                                            "rgb" => "FF9BBB59"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "COSTOS X BULTO USD",
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
                                            "rgb" => "FFFF5050"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "COSTOS X TON USD",
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
                                            "rgb" => "FFFF5050"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "VENTA SUBSIDIADA X TON USD",
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
                                            "rgb" => "FFFF5050"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "MARGEN X TON USD",
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
                                            "rgb" => "FFFF5050"
                                        )
                                    )
                                    
                                )
                            ),

                            array(
                                "value" => "TC",
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
                                            "rgb" => "FFFF5050"
                                        )
                                    )
                                    
                                )
                            ),
                            
                        );

                        $nuevoArray[0]['data'][] = $arrayFilaExcel;
                    }


                    // METROS / HOJAS / MILILITROS / PAÑOS

                    if(is_numeric($descargarSde->profactorconversionpaquetes)){
                        $profactorconversionpaquetes = doubleval($descargarSde->profactorconversionpaquetes);
                    }else{
                        $profactorconversionpaquetes = 0;
                    }

                    if(is_numeric($descargarSde->prounidadeshojasxpaquete)){
                        $prounidadeshojasxpaquete = doubleval($descargarSde->prounidadeshojasxpaquete);
                    }else{
                        $prounidadeshojasxpaquete = 0;
                    }

                    if(is_numeric($descargarSde->prometrosxunidad)){
                        $prometrosxunidad = doubleval($descargarSde->prometrosxunidad);
                    }else{
                        $prometrosxunidad = 0;
                    }

                    $metrosHojasMililitrosPanos = $profactorconversionpaquetes * $prounidadeshojasxpaquete * $prometrosxunidad;

                    // CANTIDAD EN TONS

                    if(is_numeric($descargarSde->propeso)){
                        $propeso = doubleval($descargarSde->propeso);
                    }else{
                        $propeso = 0;
                    }

                    if(is_numeric($descargarSde->sdebultosacido)){
                        $sdebultosacido = doubleval($descargarSde->sdebultosacido);
                    }else{
                        $sdebultosacido = 0;
                    }

                    $cantidadTons = (($propeso * $sdebultosacido)/1000);

                    // PVP POR METRO

                    if(is_numeric($descargarSde->sdepvpigv)){
                        $sdepvpigv = doubleval($descargarSde->sdepvpigv);
                    }else{
                        $sdepvpigv = 0;
                    }

                    $metrosHojasMililitrosPanos = $metrosHojasMililitrosPanos;

                    $pvppormetro = (($sdepvpigv / $metrosHojasMililitrosPanos ) * 100);

                    // COSTOS X TON

                    if(is_numeric($descargarSde->cbutotal)){
                        $cbutotal = doubleval($descargarSde->cbutotal);
                    }else{
                        $cbutotal = 0;
                    }

                    if(is_numeric($descargarSde->propeso)){
                        $propeso = doubleval($descargarSde->propeso);
                    }else{
                        $propeso = 0;
                    }

                    $costosxton = (($cbutotal/$propeso)*1000);

                    // VENTA SUBSIDIADA X TON

                    if(is_numeric($descargarSde->sdepcsubsidiado)){
                        $sdepcsubsidiado = doubleval($descargarSde->sdepcsubsidiado);
                    }else{
                        $sdepcsubsidiado = 0;
                    }

                    $ticcambio = $ticcambio;

                    if(is_numeric($descargarSde->propeso)){
                        $propeso = doubleval($descargarSde->propeso);
                    }else{
                        $propeso = 0;
                    }

                    $ventasubsidiadaxton = (($sdepcsubsidiado / $ticcambio)/$propeso)*1000;

                    // MARGEN X TON

                    $margenxton = $ventasubsidiadaxton - $costosxton;
                    $margenxton = floatval($margenxton);

                    $colorLetraMargenXTon = "FF000000";
                    $rellenoLetraMargenXTon = "FFF2F2F2";
                    
                    if(is_numeric($margenxton)){
                        if($margenxton < 500){
                            $colorLetraMargenXTon = 'FF9C0006';
                            $rellenoLetraMargenXTon = 'FFFFC7CE';
                        }else if($margenxton > 1000){
                            $colorLetraMargenXTon = 'FF006100';
                            $rellenoLetraMargenXTon = 'FFC6EFCE';
                        }else{
                            $colorLetraMargenXTon = 'FF9C6500';
                            $rellenoLetraMargenXTon = 'FFFFEB9C';
                        }
                        
                    }

                    // AMARILLO
                    // LETRA CAFE -> #9C6500
                    // RELLENO AMARILLO -> #FFEB9C

                    // ROJO
                    // RELLENO FFC7CE
                    // LETRA 9C0006

                    // VERDE
                    // LETRA 006100
                    // RELLENO C6EFCE





                    date_default_timezone_set("America/Lima");
                    $fechaActual = date('Y-m-d');
                    $dateuno = new DateTime($fechaActual);

                    $otraFecha = $descargarSde->sdeinicio;
                    $datedos = new \DateTime(date("Y-m-d", strtotime($otraFecha."-01")));
                    $diff = $dateuno->diff($datedos);

                    $diferenciaDias = $diff->days;


                    $arrayFilaExcel = array(
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
                            "value" => $diferenciaDias,
                            "style" => array(
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
                            "value" => $descargarSde->fecmesnumero,
                            "style" => array(
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
                            "value" => $descargarSde->clitv,
                            "style" => array(
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
                            "value" => $descargarSde->clihml,
                            "style" => array(
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
                            "value" => $descargarSde->csosubcliente,
                            "style" => array(
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
                            "value" => $descargarSde->csonombrecomercial,
                            "style" => array(
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
                            "value" => $descargarSde->csosectorpbi,
                            "style" => array(
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
                            "value" => $descargarSde->csosegmento,
                            "style" => array(
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
                            "value" => $descargarSde->csosubsegmento,
                            "style" => array(
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
                            "value" => $descargarSde->coscodigo." ".$descargarSde->cosnombre,
                            "style" => array(
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
                            "value" => $descargarSde->pronombre,
                            "style" => array(
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
                            "value" => floatval($descargarSde->profactorconversionpaquetes),
                            "style" => array(
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
                            "value" => floatval($descargarSde->prounidadeshojasxpaquete),
                            "style" => array(
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
                            "value" => floatval($descargarSde->prometrosxunidad),
                            "style" => array(
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
                            "value" => floatval($metrosHojasMililitrosPanos),
                            "style" => array(
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
                            "value" => floatval($descargarSde->propeso),
                            "style" => array(
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
                            "value" => floatval($cantidadTons),
                            "style" => array(
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
                            "value" => floatval($pvppormetro),
                            "style" => array(
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
                            "value" => floatval($descargarSde->cbutotal),
                            "style" => array(
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
                            "value" => floatval($costosxton),
                            "style" => array(
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
                            "value" => floatval($ventasubsidiadaxton),
                            "style" => array(
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
                            "value" => floatval($margenxton),
                            "style" => array(
                                "font" => array(
                                    "sz" => "9",
                                    "bold" => true,
                                    "color" => $colorLetraMargenXTon
                                ),
                                "fill" => array(
                                    "patternType" => 'solid',
                                    "fgColor" => array(
                                        "rgb" => $rellenoLetraMargenXTon
                                    )
                                ),
                                "numFmt" => "#,##0.00"
                            )
                        ),

                        array(
                            "value" => $ticcambio,
                            "style" => array(
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

            }else{

            }

            $arrayCli = array();

            $data  = $nuevoArray;








































        }


        $requestsalida = response()->json([
            "links" => $links,
            "fecs" => $fecs,
            'data' => $data,
            'descargarHistorico' => $descargarHistorico
        ]);

        return $requestsalida;

    }
}
