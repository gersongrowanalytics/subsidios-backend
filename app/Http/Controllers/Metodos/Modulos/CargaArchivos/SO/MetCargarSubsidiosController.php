<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos\SO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\usuusuarios;
use App\Models\fecfechas;
use App\Models\proproductos;
use App\Models\cliclientes;
use App\Models\sdesubsidiosdetalles;
use App\Models\espestadospendientes;
use App\Models\areareasestados;
use App\Models\carcargasarchivos;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use \DateTime;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarSubsidiosController extends Controller
{
    public function MetCargarSubsidios(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => 0,
            "FECHA_NO_REGISTRADA" => "",
            "CLIENTES_NO_ENCONTRADOS" => [],
            "PRODUCTOS_NO_ENCONTRADOS" => [],
            "SUBSIDIOS_NO_ENCONTRADOS" => []
        );

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "El archivo se subio correctamente";
        $datos          = [];
        $mensajeDetalle = "";

        // $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        
        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $reiniciartodo  = $request['reiniciartodo'];
        $archivo  = $_FILES['file']['name'];

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);

        $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

        $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/SO/Subsidios/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);;
        $fichero_subido = base_path().'/public'.$ubicacionArchivo;

        $ex_file_name = explode(".", $_FILES['file']['name']);

        $carn = new carcargasarchivos;
        $carn->tcaid        = 2;
        $carn->usuid        = $usu->usuid;
        $carn->carnombre    = $_FILES['file']['name'];
        $carn->carextension = $ex_file_name[1];
        $carn->carurl       = env('APP_URL').$ubicacionArchivo;
        $carn->carexito     = 0;
        $carn->save();
        $carid = $carn->carid;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {

            $data = [
                'archivo' => $_FILES['file']['name'], "tipo" => "Subsidios Reconocidos", "usuario" => $usu->usuusuario,
                "url_archivo" => env('APP_URL').$ubicacionArchivo
            ];
            Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

            $objPHPExcel    = IOFactory::load($fichero_subido);
            $objPHPExcel->setActiveSheetIndex(0);
            $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

            $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

            $ex_anio = $objPHPExcel->getActiveSheet()->getCell('C2')->getCalculatedValue();
            $ex_mes  = $objPHPExcel->getActiveSheet()->getCell('C3')->getCalculatedValue();


            
            // $fec = fecfechas::where('fecmesabreviacion', $ex_mes)
            //                 ->where('fecanionumero', $ex_anio)
            //                 ->where('fecdianumero', "1")
            //                 ->first(['fecid']);
            $fec = fecfechas::where('fecmesabierto', true)->first(['fecid']);

            if($fec){
                
                $cargarDataAutomatico = false;
                $cargarDataManual = false;


                for ($i=6; $i <= $numRows ; $i++) {

                    $ex_zona                = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                    $ex_territorio          = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                    $ex_cliente             = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                    $ex_codigosolicitante   = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                    $ex_codigodestinatario  = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                    $ex_sectoruno  = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();

                    $ex_segmentocliente     = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
                    $ex_subsegmentocliente  = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                    $ex_rucsubcliente       = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
                    $ex_subcliente          = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();
                    $ex_nombrecomercial     = $objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue();
                    $ex_sector              = $objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue();
                    $ex_codigouni           = $objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue();
                    $ex_descripcion         = $objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue();
                    $ex_pcsapfinal          = $objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue();
                    $ex_dsctouno            = $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue();
                    $ex_pcsubsidiado        = $objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue();
                    $ex_mup                 = $objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue();
                    $ex_pvpigv              = $objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue();
                    $ex_dsctodos            = $objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue();
                    $ex_destrucsap          = $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue();

                    $ex_inicio              = $objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue();
                    $ex_inicio = Date::excelToDateTimeObject($ex_inicio);
                    $ex_inicio = json_encode($ex_inicio);
                    $ex_inicio = json_decode($ex_inicio);
                    $ex_inicio = date("Y-m", strtotime($ex_inicio->date));

                    $ex_bultosacordados     = $objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue();

                    // 
                    $ex_cantidadbultos      = $objPHPExcel->getActiveSheet()->getCell('Y'.$i)->getCalculatedValue();
                    $ex_montoareconocer     = $objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue();

                    // CAMPOS OTORGADOS POR SAC
                    $ex_cantidadbultosreal  = $objPHPExcel->getActiveSheet()->getCell('AA'.$i)->getCalculatedValue();
                    $ex_montoareconocerreal = $objPHPExcel->getActiveSheet()->getCell('AB'.$i)->getCalculatedValue();

                    $ex_status              = $objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue();
                    $ex_diferenciaahorrocliente = $objPHPExcel->getActiveSheet()->getCell('AD'.$i)->getCalculatedValue();
                    $ex_bonificado = $objPHPExcel->getActiveSheet()->getCell('AE'.$i)->getCalculatedValue();

                    $subirData = false;

                    if(isset($ex_cantidadbultos) && $ex_cantidadbultos > 0 ){
                        $subirData = true;
                    }else if(isset($ex_cantidadbultosreal) && $ex_cantidadbultosreal > 0){
                        $subirData = true;
                    }

                    if($subirData == true){
                        if(isset($ex_codigouni)){
                            $pro = proproductos::where('prosku', $ex_codigouni)->first(['proid']);
                            // $pro = true;
            
                            if($pro){
                                $cli = cliclientes::where('clicodigoshipto', $ex_codigodestinatario)
                                                    ->first(['cliid', 'cliclientesac', 'clicodigoshipto', 'clishipto', 'clihml']);
                                // $cli = true;
            
                                if($cli){
    
                                    // 
    
                                    $esp = espestadospendientes::where('espbasedato', "Subsidio Reconocido (Plantilla Manual)")
                                                                ->where('fecid', $fec->fecid)
                                                                ->first();

                                    $espAutomatico = espestadospendientes::where('espbasedato', "Subsidio Reconocido (Plantilla Automatico)")
                                                                ->where('fecid', $fec->fecid)
                                                                ->first();
    
                                    if($esp){
    
                                        $are = espestadospendientes::join('areareasestados as are', 'are.areid', 'espestadospendientes.areid')
                                                                ->where('fecid', $fec->fecid)
                                                                ->where('are.arenombre', "SAC Sell Out Detalle")
                                                                ->first([
                                                                    'are.areid'
                                                                ]);
    
                                        // $espe = espestadospendientes::where('espbasedato', $cli->clishipto)
                                        $espe = espestadospendientes::where('cliid', $cli->cliid)
                                                                    ->where('fecid', $fec->fecid)
                                                                    // ->where('areid', $esp->areid)
                                                                    ->where('areid', $are->areid)
                                                                    ->first();
    
                                        if($espe){
                                            
                                            if($usu->perid == 1 || $usu->perid == 3 || $usu->perid == 7 || $usu->perid == 10){
                    
                                            }else{
                                                $espe->perid = $usu->perid;
                                            }
                                            $espe->espfechactualizacion = $fechaActual;
                                            if($espe->espfechaprogramado == null){
                                                $espe->espdiaretraso = "0";
                                            }else{
    
                                                $fecha_carga_real = date("Y-m-d", strtotime($espe->espfechaprogramado));
    
                                                $date1 = new DateTime($fechaActual);
                                                $date2 = new DateTime($fecha_carga_real);
    
                                                if($date1 > $date2){
                                                    $diff = $date1->diff($date2);
            
                                                    if($diff->days > 0){
                                                        $espe->espdiaretraso = $diff->days;
                                                    }else{
                                                        $espe->espdiaretraso = "0";
                                                    }
            
                                                }else{
                                                    $espe->espdiaretraso = "0";
                                                }
                                            }
    
                                            $espe->update();
    
                                        }else{
    
                                            $espultimo = espestadospendientes::orderby('espid', 'desc')->first();
                                            $pkid = $espultimo->espid + 1;
    
                                            $espn = new espestadospendientes;
                                            $espn->espid = $pkid;
                                            $espn->cliid = $cli->cliid;
                                            $espn->fecid = $fec->fecid;
                                            $espn->perid = $usu->perid;
                                            $espn->areid = $are->areid;
                                            // $espn->areid = 11;
    
                                            if($cli->cliclientesac == 1){
                                                $espn->espfechaprogramado = $esp->espfechaprogramado;
                                                $cargarDataManual = true;
                                            }else{
                                                // $espn->espfechaprogramado = "2021-08-11";
                                                $espn->espfechaprogramado = $espAutomatico->espfechaprogramado;
                                                $cargarDataAutomatico = true;
                                            }
    
                                            $espn->espchacargareal = null;
                                            $espn->espfechactualizacion = $fechaActual;
                                            // $espn->espbasedato = $cli->clishipto;
                                            $espn->espbasedato = "";
                                            $espn->espresponsable = "SAC";
    
                                            $date1 = new DateTime($fechaActual);
                                            $fecha_carga_real = date("Y-m-d", strtotime($espn->espfechaprogramado));
                                            $date2 = new DateTime($fecha_carga_real);
    
                                            if($date1 > $date2){
                                                $diff = $date1->diff($date2);
    
                                                if($diff->days > 0){
                                                    $espn->espdiaretraso = $diff->days;
                                                }else{
                                                    $espn->espdiaretraso = "0";
                                                }
    
                                            }else{
                                                $espn->espdiaretraso = "0";
                                            }
    
                                            $espn->save();
    
                                            $aree = areareasestados::where('areid', $esp->areid)->first();
    
                                            if($aree){
                                                $aree->areporcentaje = $aree->areporcentaje + 1;
                                                $aree->update();
                                            }
    
                                        }   
                                    }
    
                                    // 
    
    
                                    $sdee = sdesubsidiosdetalles::where('fecid', $fec->fecid)
                                                            ->where('sdedestrucsap', $ex_destrucsap)
                                                            ->first();
    
                                    if($sdee){
                                        
                                        if($ex_cantidadbultos){
    
                                            if(is_numeric($ex_cantidadbultos)){
                                                $sdee->sdecantidadbultos  = $ex_cantidadbultos;
                                                $sdee->sdemontoareconocer = $ex_cantidadbultos * $sdee->sdedsctodos;
                                            }else{
                                                $sdee->sdecantidadbultos  = 0;
                                                $sdee->sdemontoareconocer = 0;    
                                            }
    
                                        }else{
                                            $sdee->sdecantidadbultos  = 0;
                                            $sdee->sdemontoareconocer = 0;
                                        }
    
                                        if($cli->cliclientesac == 1){
                                            $sdee->sdesac = true;
                                            $sdee->sdeaprobado = true;
    
                                            if($ex_cantidadbultosreal){
    
                                                if(is_numeric($ex_cantidadbultosreal)){
                                                    $sdee->sdecantidadbultosreal  = $ex_cantidadbultosreal;
                                                    $sdee->sdemontoareconocerreal = $ex_cantidadbultosreal * $sdee->sdedsctodos;
                                                }else{
                                                    $sdee->sdecantidadbultosreal  = 0;
                                                    $sdee->sdemontoareconocerreal = 0;
                                                }
    
                                            }else{
                                                $sdee->sdecantidadbultosreal  = 0;
                                                $sdee->sdemontoareconocerreal = 0;
                                            }
    
                                            if($ex_cantidadbultos == $ex_cantidadbultosreal){
                                                $sdee->sdestatus = "OK";
                                            }else{
                                                $sdee->sdestatus = "ERROR CANTIDADES";
                                            }
    
                                            // $sdee->sdestatus = $ex_status;
                                            $sdee->sdediferenciaahorro = $ex_diferenciaahorrocliente;
            
                                        }else{
                                            $sdee->sdesac = false;
                                            $sdee->sdeaprobado = false;
    
                                            $sdee->sdecantidadbultosreal  = 0;
                                            $sdee->sdemontoareconocerreal = 0;
                                        }

                                        $sdee->sdebonificacion = $ex_bonificado;
    
                                        $sdee->update();
                                        
                                    }else{
                                        $respuesta = false;
                                        $mensaje = "Lo sentimos, hubieron algunos subsidios que no se encontraron en la plantilla";
                                        $logs["SUBSIDIOS_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["SUBSIDIOS_NO_ENCONTRADOS"], $ex_destrucsap, $i);
                                    }
            
                                }else{
                                    $respuesta = false;
                                    $mensaje = "Lo sentimos, hubieron algunos codigos de solicitante que no se encontraron registrados, recomendamos actualizar la maestra de clientes e intentar nuevamente gracias.";
                                    // $logs["CLIENTES_NO_ENCONTRADOS"][] = array("codigocliente" => $ex_codigodestinatario, "linea" => $i);
                                    $logs["CLIENTES_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["CLIENTES_NO_ENCONTRADOS"], $ex_codigodestinatario, $i);
                                }
            
                            }else{
                                $respuesta = false;
                                $mensaje = "Lo sentimos, hubieron algunos skus que no se encontraron registrados, recomendamos actualizar la maestra de productos e intentar nuevamente gracias.";
                                $logs["PRODUCTOS_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["PRODUCTOS_NO_ENCONTRADOS"], $ex_codigouni, $i);
                            }
                        }else{
    
                        }
                    }
                }

                if($cargarDataAutomatico == true){
                    $espe = espestadospendientes::where('espbasedato', "Subsidio Reconocido (Plantilla Automatico)")
                                                ->where('fecid', $fec->fecid)
                                                ->first();

                    if($espe){
                        if($usu->perid == 1 || $usu->perid == 3 || $usu->perid == 7 || $usu->perid == 10){
                    
                        }else{
                            $espe->perid = $usu->perid;
                        }
                        $espe->espfechactualizacion = $fechaActual;

                        $date1 = new DateTime($fechaActual);
                        $fecha_carga_real = date("Y-m-d", strtotime($espe->espfechaprogramado));
                        $date2 = new DateTime($fecha_carga_real);
                        
                        if($date1 > $date2){
                            $diff = $date1->diff($date2);

                            if($diff->days > 0){
                                $espe->espdiaretraso = $diff->days;
                            }else{
                                $espe->espdiaretraso = "0";
                            }
                        }else{
                            $espe->espdiaretraso = "0";
                        }

                        $espe->update();
                    }
                }

                if($cargarDataManual == true){
                    $espe = espestadospendientes::where('espbasedato', "Subsidio Reconocido (Plantilla Manual)")
                                                ->where('fecid', $fec->fecid)
                                                ->first();

                    if($espe){
                        if($usu->perid == 1 || $usu->perid == 3 || $usu->perid == 7 || $usu->perid == 10){
                    
                        }else{
                            $espe->perid = $usu->perid;
                        }
                        $espe->espfechactualizacion = $fechaActual;

                        $date1 = new DateTime($fechaActual);
                        $fecha_carga_real = date("Y-m-d", strtotime($espe->espfechaprogramado));
                        $date2 = new DateTime($fecha_carga_real);
                        
                        if($date1 > $date2){
                            $diff = $date1->diff($date2);

                            if($diff->days > 0){
                                $espe->espdiaretraso = $diff->days;
                            }else{
                                $espe->espdiaretraso = "0";
                            }
                        }else{
                            $espe->espdiaretraso = "0";
                        }

                        $espe->update();
                    }
                }

                $care = carcargasarchivos::find($carid);
                $care->carexito = 1;
                $care->update();

            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, el mes o año asignado no se encuentra habilitado, recomendamos actualizar e intentar nuevamente gracias";
                $logs["FECHA_NO_REGISTRADA"] = "AÑO: ".$ex_anio." MES: ".$ex_mes;
            }

        }else{
            $respuesta = false;
            $mensaje = "Lo sentimos, el archivo no se pudo guardar en el sistema";
        }

        $logs["MENSAJE"] = $mensaje;
        $logs["RESPUESTA"] = $respuesta;
        
        $requestsalida = response()->json([
            "respuesta"      => $respuesta,
            "mensaje"        => $mensaje,
            "datos"          => $datos,
            "mensajeDetalle" => $mensajeDetalle,
            "logs" => $logs,
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken, // token
            $usu->usuid, // usuid
            null, // audip
            $fichero_subido, // audjsonentrada
            $requestsalida,// audjsonsalida
            'CARGAR DATA DE SUBSIDIOS SO AUTOMATICOS Y MANUALES', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/so/subsidios-so-automaticos-manuales', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;
    }

    private function EliminarDuplicidad($array, $dato, $linea)
    {
        $encontroDato = false;
        foreach($array as $arr){
            if($arr['codigo'] == $dato){
                $encontroDato = true;
                break;
            }
        }

        if($encontroDato == false){
            $array[] = array("codigo" => $dato, "linea" => $linea);
        }

        return $array;
    }
}
