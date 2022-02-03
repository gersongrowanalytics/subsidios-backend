<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\usuusuarios;
use App\Models\proproductos;
use App\Models\catcategorias;
use App\Models\concodigosnegocios;
use App\Models\coscodigossectores;
use App\Models\marmarcas;
use App\Models\carcargasarchivos;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;
use App\Models\espestadospendientes;
use App\Models\areareasestados;
use App\Models\fecfechas;
use \DateTime;

class MetCargarMaestraProductosController extends Controller
{
    public function CargarMaestraProductos(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => "0",
            "NUEVA_CATEGORIA"     => [],
            "NUEVA_MARCA"         => [],
            "NUEVO_SECTOR"        => [],
            "NUEVO_NEGOCIO"       => [],
            "PRODUCTO_EDITADO"    => array(),
            "PRODUCTO_NO_EDITADO" => array(),
            "NUEVOS_PRODUCTO" => []
        );

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "El archivo se subio correctamente";
        $datos          = [];
        $mensajeDetalle = "";
        $mensajedev     = "";

        try{

            // $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
            $usutoken = $request->header('api_token');
            if(!isset($usutoken)){
                $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
            }
            $archivo  = $_FILES['file']['name'];

            $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);

            $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

            
            $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/Productos/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);
            $fichero_subido = base_path().'/public'.$ubicacionArchivo;

            $ex_file_name = explode(".", $_FILES['file']['name']);

            $carultimo = carcargasarchivos::orderby('carid', 'desc')->first();
            $pkcar = $carultimo->carid + 1;

            $carn = new carcargasarchivos;
            $carn->carid        = $pkcar;
            $carn->tcaid        = 9;
            $carn->usuid        = $usu->usuid;
            $carn->carnombre    = $_FILES['file']['name'];
            $carn->carextension = $ex_file_name[1];
            $carn->carurl       = env('APP_URL').$ubicacionArchivo;
            $carn->carexito     = 0;
            $carn->save();
            $carid = $pkcar;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {
                
                $data = [
                    'archivo' => $_FILES['file']['name'], "tipo" => "Maestra de Productos", "usuario" => $usu->usuusuario,
                    "url_archivo" => env('APP_URL').$ubicacionArchivo
                ];
                Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

                // OBTENER CODIGOS DE NEGOCIOS
                $conEstaticos = array(
                    array(
                        "id" => 0,
                        "codigo" => "NO ES NINGUNO",
                        "negocio" => "NO ES NINGUNO",
                    )
                );

                // OBTENER CATEGORIAS
                $catEstaticos = array(
                    array(
                        "id" => 0,
                        "codigo" => "NO ES NINGUNO",
                        "categoria" => "NO ES NINGUNO",
                    )
                );

                // OBTENER MARCAS
                $marEstaticos = array(
                    array(
                        "id" => 0,
                        "marca" => "NO ES NINGUNO",
                    )
                );

                // OBTENER CODIGOS DE SECTORES
                $cosEstaticos = array(
                    array(
                        "id" => 0,
                        "codigo" => "NO ES NINGUNO",
                        "sector" => "NO ES NINGUNO",
                    )
                );

                // $objPHPExcel    = IOFactory::load($fichero_subido);
                // $objPHPExcel->setActiveSheetIndex(0);
                // $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                // $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

                // $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

                // for ($i=2; $i <= $numRows ; $i++) {
                //     $codigoOrganizacion  = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                //     $nombreOrganizacion  = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                //     $codigoProductoSoft  = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                //     $descripcionProducto = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                //     $codigoNegocio       = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                //     $nombreNegocio       = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                //     $codigoCategoria     = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                //     $nombreCategoria     = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
                //     $codigoSector        = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                //     $nombreSector        = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
                //     $segmentacion        = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();
                //     $presentacion        = $objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue();
                //     $marca               = $objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue();
                //     $conteo              = $objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue();
                //     $formato             = $objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue();
                //     $talla               = $objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue();
                //     $peso                = $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue();
                //     $mecanica            = $objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue();
                //     $factorBultos        = $objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue();
                //     $factorCajas         = $objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue();
                //     $factorPaquetes      = $objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue();
                //     $factorUnidadMinima  = $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue();
                //     $factorToneladas     = $objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue();
                //     $factorMilesUnidades = $objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue();

                //     if(!isset($nombreCategoria)){
                //         $nombreCategoria = "otros";
                //     }

                //     if(!isset($nombreNegocio)){
                //         $nombreNegocio = "otros";
                //     }

                //     if(!isset($nombreSector)){
                //         $nombreSector = "otros";
                //     }

                //     if(!isset($marca)){
                //         $marca = "otros";
                //     }
                    
                //     $catidPro = null;
                //     $maridPro = null;
                //     $cosidPro = null;
                //     $conidPro = null;
                //     $pronombrePro = $descripcionProducto;
                //     $proskuPro    = $codigoProductoSoft;
                //     $prosegmentacionPro = $segmentacion;
                //     $propresentacionPro = $presentacion;
                //     $proconteoPro   = $conteo;
                //     $proformatoPro  = $formato;
                //     $protallaPro    = $talla;
                //     $propesoPro     = $peso;
                //     $promecanicaPro = $mecanica;
                //     $profactorconversionbultosPro   = $factorBultos;
                //     $profactorconversioncajasPro    = $factorCajas;
                //     $profactorconversionpaquetesPro = $factorPaquetes;
                //     $profactorconversionunidadminimaindivisiblePro = $factorUnidadMinima;
                //     $profactorconversiontoneladasPro     = $factorToneladas;
                //     $profactorconversionmilesunidadesPro = $factorMilesUnidades;

                //     // 
                //     $encontroCat = false;

                //     foreach($catEstaticos as $catEstatico){
                //         if($catEstatico['categoria'] == $nombreCategoria){
                //             $encontroCat = true;
                //             $catidPro = $catEstatico['id'];
                //             break;
                //         }
                //     }

                //     if($encontroCat == false){

                //         $cat = catcategorias::where('catnombre', $nombreCategoria )->first();

                //         if($cat){

                //             $encontroCat = true;
                //             $catidPro = $cat->catid;

                //             $catEstaticos[] = array(
                //                 "id" => $cat->catid,
                //                 "codigo" => $cat->catcodigo,
                //                 "categoria" => $cat->catnombre,
                //             );

                //         }else{

                //             $catn = new catcategorias;
                //             $catn->catnombre = $nombreCategoria;
                //             $catn->catcodigo = $codigoCategoria;
                //             if($catn->save()){
                //                 $encontroCat = true;
                //                 $catidPro = $catn->catid;

                //                 $catEstaticos[] = array(
                //                     "id" => $catn->catid,
                //                     "codigo" => $catn->catcodigo,
                //                     "categoria" => $catn->catnombre,
                //                 );
                //             }
                //         }

                //     }

                //     // 
                //     $encontroCon = false;

                //     foreach($conEstaticos as $conEstatico){
                //         if($conEstatico['negocio'] == $nombreNegocio){
                //             $encontroCon = true;
                //             $conidPro = $conEstatico['id'];
                //             break;
                //         }
                //     }

                //     if($encontroCon == false){
                //         $con = concodigosnegocios::where('connombre', $nombreNegocio)->first();

                //         if($con){

                //             $encontroCon = true;
                //             $conidPro = $con->conid;

                //             $conEstaticos[] = array(
                //                 "id" => $con->conid,
                //                 "codigo"  => $con->concodigo,
                //                 "negocio" => $con->connombre,
                //             );

                //         }else{
                //             $conn = new concodigosnegocios;
                //             $conn->concodigo = $codigoNegocio;
                //             $conn->connombre = $nombreNegocio;
                //             if($conn->save()){
                //                 $encontroCon = true;
                //                 $conidPro = $conn->conid;

                //                 $conEstaticos[] = array(
                //                     "id" => $conn->conid,
                //                     "codigo"  => $conn->concodigo,
                //                     "negocio" => $conn->connombre,
                //                 );
                //             }
                //         }
                //     }

                //     // 
                //     $encontroCos = false;
                //     foreach($cosEstaticos as $cosEstatico){
                //         if($cosEstatico['sector'] == $nombreSector){
                //             $encontroCos = true;
                //             $cosidPro = $cosEstatico['id'];
                //             break;
                //         }
                //     }

                //     if($encontroCos == false){
                //         $cos = coscodigossectores::where('cosnombre', $nombreSector)->first();

                //         if($cos){
                //             $cosidPro = $cos->cosid;
                //             $encontroCos = true;
                //             $cosEstaticos[] = array(
                //                 "id" => $cos->cosid,
                //                 "codigo" => $cos->coscodigo,
                //                 "sector" => $cos->cosnombre,
                //             );
                //         }else{

                //             $cosn = new coscodigossectores;
                //             $cosn->coscodigo = $codigoSector;
                //             $cosn->cosnombre = $nombreSector;
                //             if($cosn->save()){
                //                 $cosidPro = $cosn->cosid;
                //                 $encontroCos = true;
                //                 $cosEstaticos[] = array(
                //                     "id" => $cosn->cosid,
                //                     "codigo" => $cosn->coscodigo,
                //                     "sector" => $cosn->cosnombre,
                //                 );
                //             }

                //         }

                //     }

                //     // 
                //     $encontroMar = false;

                //     foreach($marEstaticos as $marEstatico){
                //         if($marEstatico['marca'] == $marca){
                //             $encontroMar = true;
                //             $maridPro = $marEstatico['id'];
                //             break;
                //         }
                //     }

                //     if($encontroMar == false){
                //         $mar = marmarcas::where('marnombre', $marca)->first();
                //         if($mar){
                //             $encontroMar = true;
                //             $maridPro = $mar->marid;
                            
                //             $marEstaticos[] = array(
                //                 "id" => $mar->marid,
                //                 "marca" => $mar->marnombre,
                //             );

                //         }else{
                //             $marn = new marmarcas;
                //             $marn->marnombre = $marca;
                //             if($marn->save()){
                //                 $encontroMar = true;
                //                 $maridPro = $marn->marid;
                                
                //                 $marEstaticos[] = array(
                //                     "id" => $marn->marid,
                //                     "marca" => $marn->marnombre,
                //                 );
                //             }
                //         }
                //     }

                //     $pro = proproductos::where('prosku', $codigoProductoSoft)->first();

                //     if($pro){

                //         $camposEditados = [];

                //         if($pro->catid != $catidPro){
                //             $camposEditados[] = "CATEGORIA ANTES: ".$pro->catid." AHORA: ".$catidPro;
                //             $pro->catid = $catidPro;
                //         }

                //         if($pro->marid != $maridPro){
                //             $camposEditados[] = "MARCA ANTES: ".$pro->marid." AHORA: ".$maridPro;
                //             $pro->marid = $maridPro;
                //         }
                        
                //         if($pro->cosid != $cosidPro){
                //             $camposEditados[] = "COS ANTES: ".$pro->cosid." AHORA: ".$cosidPro;
                //             $pro->cosid = $cosidPro;
                //         }

                //         if($pro->conid != $conidPro){
                //             $camposEditados[] = "CON ANTES: ".$pro->conid." AHORA: ".$conidPro;
                //             $pro->conid = $conidPro;
                //         }

                //         if($pro->pronombre != $pronombrePro){
                //             $camposEditados[] = "NOMBRE ANTES: ".$pro->pronombre." AHORA: ".$pronombrePro;
                //             $pro->pronombre = $pronombrePro;
                //         }

                //         // $pro->prosku = $proskuPro;
                //         if($pro->prosegmentacion != $prosegmentacionPro){
                //             $camposEditados[] = "SEGMENTACION ANTES: ".$pro->prosegmentacion." AHORA: ".$prosegmentacionPro;
                //             $pro->prosegmentacion = $prosegmentacionPro;
                //         }
                        
                //         if($pro->propresentacion != $propresentacionPro){
                //             $camposEditados[] = "PRESENTACION ANTES: ".$pro->prosegmentacion." AHORA: ".$propresentacionPro;
                //             $pro->propresentacion = $propresentacionPro;
                //         }
                        
                //         if($pro->proconteo != $proconteoPro){
                //             $camposEditados[] = "CONTEO ANTES: ".$pro->proconteo." AHORA: ".$proconteoPro;
                //             $pro->proconteo = $proconteoPro;
                //         }

                //         if($pro->proformato != $proformatoPro){
                //             $camposEditados[] = "FORMATO ANTES: ".$pro->proformato." AHORA: ".$proformatoPro;
                //             $pro->proformato = $proformatoPro;
                //         }
                        
                //         if($pro->protalla != $protallaPro){
                //             $camposEditados[] = "TALLA ANTES: ".$pro->protalla." AHORA: ".$protallaPro;
                //             $pro->protalla = $protallaPro;
                //         }

                //         if("$pro->propeso" != "$propesoPro"){
                //             $camposEditados[] = "PESO ANTES: ".$pro->propeso." AHORA: ".$propesoPro;
                //             $pro->propeso = $propesoPro;
                //         }

                //         if($pro->promecanica != $promecanicaPro){
                //             $camposEditados[] = "MECANICA ANTES: ".$pro->promecanica." AHORA: ".$promecanicaPro;
                //             $pro->promecanica = $promecanicaPro;
                //         }

                //         if($pro->profactorconversionbultos != $profactorconversionbultosPro){
                //             $camposEditados[] = "BULTOS ANTES: ".$pro->profactorconversionbultos." AHORA: ".$profactorconversionbultosPro;
                //             $pro->profactorconversionbultos = $profactorconversionbultosPro;
                //         }

                //         if($pro->profactorconversioncajas != $profactorconversioncajasPro){
                //             $camposEditados[] = "CAJAS ANTES: ".$pro->profactorconversioncajas." AHORA: ".$profactorconversioncajasPro;
                //             $pro->profactorconversioncajas = $profactorconversioncajasPro;
                //         }

                //         if($pro->profactorconversionpaquetes != $profactorconversionpaquetesPro){
                //             $camposEditados[] = "PAQUETES ANTES: ".$pro->profactorconversionpaquetes." AHORA: ".$profactorconversionpaquetesPro;
                //             $pro->profactorconversionpaquetes = $profactorconversionpaquetesPro;
                //         }

                //         if($pro->profactorconversionunidadminimaindivisible != $profactorconversionunidadminimaindivisiblePro){
                //             $camposEditados[] = "INDIVISIBLE ANTES: ".$pro->profactorconversionunidadminimaindivisible." AHORA: ".$profactorconversionunidadminimaindivisiblePro;
                //             $pro->profactorconversionunidadminimaindivisible = $profactorconversionunidadminimaindivisiblePro;
                //         }
                        
                //         if("$pro->profactorconversiontoneladas" != "$profactorconversiontoneladasPro"){
                //             $camposEditados[] = "TONELADAS ANTES: ".$pro->profactorconversiontoneladas." AHORA: ".$profactorconversiontoneladasPro;
                //             $pro->profactorconversiontoneladas = $profactorconversiontoneladasPro;
                //         }
                        
                //         if($pro->profactorconversionmilesunidades != $profactorconversionmilesunidadesPro){
                //             $camposEditados[] = "UNIDADES ANTES: ".$pro->profactorconversionmilesunidades." AHORA: ".$profactorconversionmilesunidadesPro;
                //             $pro->profactorconversionmilesunidades = $profactorconversionmilesunidadesPro;
                //         }

                //         if(sizeof($camposEditados) > 0){
                //             if($pro->update()){

                //                 $logs["PRODUCTO_EDITADO"][] = array(
                //                     "proid" => $pro->proid,
                //                     "sku" => $pro->prosku,
                //                     "camposEditados" => $camposEditados
                //                 );
    
                //             }else{
                //                 $respuesta = false;
                //                 $mensaje = "Lo sentimos, el producto no se pudo editar en el sistema. LINEA : ".$i;
                //             }
                //         }else{
                //             $logs["PRODUCTO_NO_EDITADO"][] = array(
                //                 "proid" => $pro->proid,
                //                 "sku" => $pro->prosku,
                //                 "camposEditados" => $camposEditados
                //             );
                //         }

                //     }else{

                //         $pron = new proproductos;
                //         $pron->catid = $catidPro;
                //         $pron->marid = $maridPro;
                //         $pron->cosid = $cosidPro;
                //         $pron->conid = $conidPro;
                //         $pron->pronombre = $pronombrePro;
                //         $pron->prosku = $proskuPro;
                //         $pron->prosegmentacion = $prosegmentacionPro;
                //         $pron->propresentacion = $propresentacionPro;
                //         $pron->proconteo = $proconteoPro;
                //         $pron->proformato = $proformatoPro;
                //         $pron->protalla = $protallaPro;
                //         $pron->propeso = $propesoPro;
                //         $pron->promecanica = $promecanicaPro;
                //         $pron->profactorconversionbultos = $profactorconversionbultosPro;
                //         $pron->profactorconversioncajas = $profactorconversioncajasPro;
                //         $pron->profactorconversionpaquetes = $profactorconversionpaquetesPro;
                //         $pron->profactorconversionunidadminimaindivisible = $profactorconversionunidadminimaindivisiblePro;
                //         $pron->profactorconversiontoneladas = $profactorconversiontoneladasPro;
                //         $pron->profactorconversionmilesunidades = $profactorconversionmilesunidadesPro;
                //         if($pron->save()){
                //             $logs['NUEVOS_PRODUCTO'][] = "PROID: ".$pron->proid." | SKU: ".$pron->prosku;
                //         }else{
                //             $respuesta = false;
                //             $mensaje = "Lo sentimos, el producto no se pudo guardar en el sistema. LINEA : ".$i;            
                //         }
                //     }

                // }


                if($usu->usuid != 1){

                    $care = carcargasarchivos::find($carid);
                    $care->carexito = 1;
                    $care->update();


                    $fec = fecfechas::where('fecmesabierto', true)->first(['fecid']);
                    $fecid = $fec->fecid;

                    $espe = espestadospendientes::where('fecid', $fecid)
                                                ->where('espbasedato', "Master Productos")
                                                ->first();

                    if($espe){
                        if($usu->perid == 1 || $usu->perid == 3 || $usu->perid == 7 || $usu->perid == 10){
                            
                        }else{
                            $espe->perid = $usu->perid;
                            $espe->espfechactualizacion = $fechaActual;

                            $date1 = new DateTime($fechaActual);
                            $fecha_carga_real = date("Y-m-d", strtotime($espe->espfechaprogramado));
                            $date2 = new DateTime($fecha_carga_real);

                            $diff = $date1->diff($date2);

                            if($date1 > $date2){
                                if($diff->days > 0){
                                    $espe->espdiaretraso = $diff->days;
                                }else{
                                    $espe->espdiaretraso = "0";
                                }
                            }else{
                                $espe->espdiaretraso = "0";
                            }

                            $espe->update();


                            $aree = areareasestados::where('areid', $espe->areid)->first();

                            if($aree){

                                $espcount = espestadospendientes::where('fecid', $fecid)
                                                    ->where('areid', $espe->areid)
                                                    ->where('espfechactualizacion', '!=', null)
                                                    ->count();

                                if($espcount == 0){
                                    $aree->areporcentaje = "100";
                                }else{
                                    // TOTAL 4
                                    $porcentaje = (100*$espcount)/4;
                                    $aree->areporcentaje = $porcentaje;
                                }

                                $aree->update();
                            } 
                        }

                    }
                }


            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, el archivo no se pudo guardar en el sistema";
            }
        } catch (Exception $e) {
            $mensajedev = $e->getMessage();
        }
        
        $logs["MENSAJE"] = $mensaje;
        $logs["RESPUESTA"] = $respuesta;
        
        $requestsalida = response()->json([
            "respuesta"      => $respuesta,
            "mensaje"        => $mensaje,
            "datos"          => $datos,
            "mensajeDetalle" => $mensajeDetalle,
            "mensajedev" => $mensajedev,
            "logs" => $logs,
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken, // token
            $usu->usuid, // usuid
            null, // audip
            $fichero_subido, // audjsonentrada
            $requestsalida,// audjsonsalida
            'CARGAR DATA DE PRODUCTOS AL SISTEMA ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/productos', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }
}
