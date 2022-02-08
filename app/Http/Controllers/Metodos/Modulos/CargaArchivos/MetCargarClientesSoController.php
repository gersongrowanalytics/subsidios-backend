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
use App\Models\csoclientesso;
use \DateTime;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarClientesSoController extends Controller
{
    public function MetCargarClientesSo(Request $request)
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

            $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/FormatoVentas/ClientesSO/'.basename($codigoArchivoAleatorio.'-'.$fechaActual.'-'.$_FILES['file']['name']);
            $fichero_subido = base_path().'/public'.$ubicacionArchivo;


            if($usu->usuid != 1){
                
                $ex_file_name = explode(".", $_FILES['file']['name']);

                $carn = new carcargasarchivos;
                $carn->tcaid        = 12;
                $carn->usuid        = $usu->usuid;
                $carn->carnombre    = $_FILES['file']['name'];
                $carn->carextension = $ex_file_name[1];
                $carn->carurl       = env('APP_URL').$ubicacionArchivo;
                $carn->carexito     = 0;
                $carn->save();
                $carid = $carn->carid;
            }

            if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {
                
                $data = [
                    'archivo' => $_FILES['file']['name'], "tipo" => "Master Clientes SO", "usuario" => $usu->usuusuario,
                    "url_archivo" => env('APP_URL').$ubicacionArchivo
                ];
                Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

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

                            $ex_codsolicitante    = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                            $ex_nombsolicitante   = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                            $ex_coddestinatario   = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                            $ex_nombdestinatario  = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                            $ex_rucsubcliente     = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                            $ex_subcliente        = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                            $ex_sectorpbi         = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                            $ex_segmentos         = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
                            $ex_subsegmento       = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                            $ex_nombrecomercial   = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();

                            $cso = csoclientesso::where('csorucsubcliente', $ex_rucsubcliente)
                                                ->where('csocoddestinatario', $ex_coddestinatario)
                                                ->first();

                            if($cso){

                                $camposEditados = [];

                                if($ex_codsolicitante != $cso->csocodsolicitante){
                                    $camposEditados[] = "COD SOLICITANTE ANTES: ".$cso->csocodsolicitante." AHORA: ".$ex_codsolicitante;
                                    $cso->csocodsolicitante = $ex_codsolicitante;
                                }

                                if($ex_nombsolicitante != $cso->csonombsolicitante){
                                    $camposEditados[] = "NOMB SOLICITANTE ANTES: ".$cso->csonombsolicitante." AHORA: ".$ex_nombsolicitante;
                                    $cso->csonombsolicitante = $ex_nombsolicitante;
                                }

                                if($ex_nombdestinatario != $cso->csonombdestinatario){
                                    $camposEditados[] = "NOMB DESTINATARIO ANTES: ".$cso->csonombdestinatario." AHORA: ".$ex_nombdestinatario;
                                    $cso->csonombdestinatario = $ex_nombdestinatario;
                                }

                                if($ex_subcliente != $cso->csosubcliente){
                                    $camposEditados[] = "SUB CLIENTE ANTES: ".$cso->csosubcliente." AHORA: ".$ex_subcliente;
                                    $cso->csosubcliente = $ex_subcliente;
                                }

                                if($ex_sectorpbi != $cso->csosectorpbi){
                                    $camposEditados[] = "SECTOR PBI ANTES: ".$cso->csosectorpbi." AHORA: ".$ex_sectorpbi;
                                    $cso->csosectorpbi = $ex_sectorpbi;
                                }

                                if($ex_segmentos != $cso->csosegmento){
                                    $camposEditados[] = "SEGMENTOS ANTES: ".$cso->csosegmento." AHORA: ".$ex_segmentos;
                                    $cso->csosegmento = $ex_segmentos;
                                }

                                if($ex_subsegmento != $cso->csosubsegmento){
                                    $camposEditados[] = "SUB SEGMENTOS ANTES: ".$cso->csosubsegmento." AHORA: ".$ex_subsegmento;
                                    $cso->csosubsegmento = $ex_subsegmento;
                                }

                                if($ex_nombrecomercial != $cso->csonombrecomercial){
                                    $camposEditados[] = "NOMBRE COMERCIAL ANTES: ".$cso->csonombrecomercial." AHORA: ".$ex_nombrecomercial;
                                    $cso->csonombrecomercial = $ex_nombrecomercial;
                                }

                                
                                if(sizeof($camposEditados) > 0){
                                    if($cso->update()){

                                        $logs["CLIENTE_SO_EDITADO"][] = array(
                                            "csoid"          => $cso->csoid,
                                            "ruc"            => $cso->csorucsubcliente,
                                            "coddest"        => $cso->csocoddestinatario,
                                            "camposEditados" => $camposEditados
                                        );
            
                                    }else{
                                        $respuesta = false;
                                        $mensaje = "Lo sentimos, el CLIENTE SO no se pudo editar en el sistema. LINEA : ".$i;
                                    }
                                }else{
                                    $logs["CLIENTE_SO_NO_EDITADO"][] = array(
                                        "csoid"          => $cso->csoid,
                                        "ruc"            => $cso->csorucsubcliente,
                                        "coddest"        => $cso->csocoddestinatario,
                                        "camposEditados" => $camposEditados
                                    );
                                }

                                

                            }else{

                                $cson = new csoclientesso;
                                $cson->csocodsolicitante   = $ex_codsolicitante;
                                $cson->csonombsolicitante  = $ex_nombsolicitante;
                                $cson->csocoddestinatario  = $ex_coddestinatario;
                                $cson->csonombdestinatario = $ex_nombdestinatario;
                                $cson->csorucsubcliente    = $ex_rucsubcliente;
                                $cson->csosubcliente       = $ex_subcliente;
                                $cson->csosectorpbi        = $ex_sectorpbi;
                                $cson->csosegmento         = $ex_segmentos;
                                $cson->csosubsegmento      = $ex_subsegmento;
                                $cson->csonombrecomercial  = $ex_nombrecomercial;
                                $cson->save();

                            }


                        }

                    }


                    // REGISTRAR ACTUALIZACION EN EL HOME
                    if($usu->usuid != 1){

                        $care = carcargasarchivos::find($carid);
                        $care->carexito = 1;
                        $care->update();
    
                        $espe = espestadospendientes::where('fecid', $fecid)
                                                    ->where('espbasedato', "Master Clientes SO")
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
            'CARGAR DATA DE CLIENTES SO', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/clientes-so', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }
}
