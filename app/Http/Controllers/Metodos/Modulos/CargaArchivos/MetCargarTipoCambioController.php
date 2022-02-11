<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\usuusuarios;
use App\Models\fecfechas;
use App\Models\espestadospendientes;
use App\Models\areareasestados;
use App\Models\carcargasarchivos;
use App\Models\tictipocambios;
use \DateTime;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarTipoCambioController extends Controller
{
    public function MetCargarTipoCambio(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "El archivo se subio correctamente";
        $datos          = [];
        $mensajeDetalle = "";
        $mensajedev = "";

        $logs = array();

        try{

            $usutoken = $request->header('api_token');
            if(!isset($usutoken)){
                $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
            }
            $archivo  = $_FILES['file']['name'];

            $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);

            $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

            $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/FormatoVentas/TipoCambio/'.basename($codigoArchivoAleatorio.'-'.$fechaActual.'-'.$_FILES['file']['name']);
            $fichero_subido = base_path().'/public'.$ubicacionArchivo;


            if($usu->usuid != 1){

                $ex_file_name = explode(".", $_FILES['file']['name']);

                $carn = new carcargasarchivos;
                $carn->tcaid        = 13;
                $carn->usuid        = $usu->usuid;
                $carn->carnombre    = $_FILES['file']['name'];
                $carn->carextension = $ex_file_name[1];
                $carn->carurl       = env('APP_URL').$ubicacionArchivo;
                $carn->carexito     = 0;
                $carn->save();
                $carid = $carn->carid;
            }

            if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {
                
                if($usu->usuid != 1){
                    $data = [
                        'archivo' => $_FILES['file']['name'], "tipo" => "Tipo de Cambio", "usuario" => $usu->usuusuario,
                        "url_archivo" => env('APP_URL').$ubicacionArchivo
                    ];
                    Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));
                }

                $fec = fecfechas::where('fecmesabierto', true)->first(['fecid']);

                if($fec){

                    $fecid = $fec->fecid;


                    if($usu->usuid == 1){

                        $objPHPExcel    = IOFactory::load($fichero_subido);
                        $objPHPExcel->setActiveSheetIndex(0);
                        $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                        $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

                        $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

                        for ($i=2; $i <= $numRows ; $i++) {

                            $ex_fecha = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                            $ex_fecha = Date::excelToDateTimeObject($ex_fecha);
                            $ex_fecha = json_encode($ex_fecha);
                            $ex_fecha = json_decode($ex_fecha);
                            $ex_fecha = date("Y-m", strtotime($ex_fecha->date));


                            $ex_tc    = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();

                            $fec = fecfechas::where('fecfecha', $ex_fecha."-1")->first();

                            if($fec){
                                
                                $tic = tictipocambios::where('ticfecha', $ex_fecha)->first();

                                if($tic){

                                    $camposEditados = [];

                                    if($tic->fecid != $fec->fecid){
                                        $camposEditados[] = "FECID ANTES: ".$tic->fecid." AHORA: ".$fec->fecid;
                                        $tic->fecid = $fec->fecid;
                                    }

                                    if($tic->tictc != $ex_tc){
                                        $camposEditados[] = "TC ANTES: ".$tic->tictc." AHORA: ".$ex_tc;
                                        $tic->tictc = $ex_tc; 
                                    }

                                    if(sizeof($camposEditados) > 0){
                                        if($tic->update()){
    
                                            $logs["TIPO_CAMBIO_EDITADO"][] = array(
                                                "ticid"     => $tic->ticid,
                                                "fecha"     => $tic->ex_fecha,
                                                "TC"        => $tic->tictc,
                                                "camposEditados" => $camposEditados
                                            );
                
                                        }else{
                                            $respuesta = false;
                                            $mensaje = "Lo sentimos, el CLIENTE SO no se pudo editar en el sistema. LINEA : ".$i;
                                        }
                                    }else{
                                        $logs["TIPO_CAMBIO_NO_EDITADO"][] = array(
                                            "ticid"     => $tic->ticid,
                                            "fecha"     => $tic->ex_fecha,
                                            "TC"        => $tic->tictc,
                                            "camposEditados" => $camposEditados
                                        );
                                    }


                                }else{

                                    $ticn = new tictipocambios;
                                    $ticn->fecid    = $fec->fecid;
                                    $ticn->ticfecha = $ex_fecha;
                                    $ticn->tictc    = $ex_tc;
                                    $ticn->save();

                                }

                            }else{

                                $respuesta = false;
                                $mensaje = "Lo sentimos, el mes o año asignado no se encuentra en los registros, recomendamos actualizar la maestra de fechas e intentar nuevamente gracias";
                                $logs["FECHA_NO_REGISTRADA"][] = "Lo sentimos no encontramos la siguiente fecha: ".$ex_fecha." PORFAVOR REVISE SI ESTA FECHA ES CORRECTA EN LA LINEA: ".$i;

                            }

                        }

                    }


                    // REGISTRAR ACTUALIZACION EN EL HOME
                    if($usu->usuid != 1){

                        $care = carcargasarchivos::find($carid);
                        $care->carexito = 1;
                        $care->update();
    
                        $espe = espestadospendientes::where('fecid', $fecid)
                                                    ->where('espbasedato', "Tipo de Cambio")
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

                                        $countBasesTotales = espestadospendientes::where('fecid', $fecid)
                                                                            ->where('areid', $espe->areid)
                                                                            ->count();

                                        $porcentaje = (100*$espcount)/$countBasesTotales;
                                        $aree->areporcentaje = round($porcentaje);
                                    }
    
                                    $aree->update();
                                } 
                            }
    
                        }else{
                            $respuesta = true;
                            $mensaje = "Lo sentimos, no encontramos el registro para actualizar la fecha en nuestro HOME";
                        }
                    }

                }else{
                    $respuesta = false;
                    $mensaje = "Lo sentimos, el mes o año asignado no se encuentra en los registros, recomendamos actualizar la maestra de fechas e intentar nuevamente gracias";
                    $logs["FECHA_NO_REGISTRADA"] = $fechaActual;
                }



            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, el archivo no se pudo guardar en el sistema";
            }


        }catch(Exception $e){
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
            'CARGAR DATA DE TIPOS DE CAMBIO', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/tipo-cambio', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }
}
