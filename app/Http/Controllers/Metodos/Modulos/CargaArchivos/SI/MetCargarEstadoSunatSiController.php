<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos\SI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\fecfechas;
use App\Models\usuusuarios;
use App\Models\fsifacturassi;
use App\Models\espestadospendientes;
use App\Models\areareasestados;
use App\Models\carcargasarchivos;
use \DateTime;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarEstadoSunatSiController extends Controller
{
    public function MetCargarEstadoSunatSi(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');
        $fecidUsada = 0;
        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => 0,
            "NO_SE_ENCONTRO_DOCUMENTO" => []
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

        $archivo  = $_FILES['file']['name'];

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);

        $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

        $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/SI/EstadoSunatFacturas/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);
        $fichero_subido = base_path().'/public'.$ubicacionArchivo;

        $ex_file_name = explode(".", $_FILES['file']['name']);
        $carn = new carcargasarchivos;
        $carn->tcaid        = 8;
        $carn->usuid        = $usu->usuid;
        $carn->carnombre    = $_FILES['file']['name'];
        $carn->carextension = $ex_file_name[1];
        $carn->carurl       = env('APP_URL').$ubicacionArchivo;
        $carn->carexito     = 0;
        $carn->save();
        $carid = $carn->carid;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {
            $data = [
                'archivo' => $_FILES['file']['name'], "tipo" => "Estado Sunat", "usuario" => $usu->usuusuario,
                "url_archivo" => env('APP_URL').$ubicacionArchivo
            ];
            Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));
            // $objPHPExcel    = IOFactory::load($fichero_subido);
            // $objPHPExcel->setActiveSheetIndex(0);
            // $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            // $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

            // $logs['NUMERO_LINEAS_EXCEL'] = $numRows;
            
            // $encontrofecha = false;

            // for ($i=2; $i <= $numRows; $i++) {

            //     $ex_anio  = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
            //     $ex_mes   = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
            //     $ex_documentocomprobante  = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
            //     $ex_estadocomprobante     = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();

            //     $fecid = 0;

            //     if($i == 2){
            //         $fec = fecfechas::where('fecmesabreviacion', $ex_mes)
            //                     ->where('fecanionumero', $ex_anio)
            //                     ->where('fecdianumero', "1")
            //                     ->first();

            //         if($fec){
            //             $fecid = $fec->fecid;
            //             $fecidUsada = $fec->fecid;
            //             $encontrofecha = true;

            //             $fsi = fsifacturassi::where('fecid', $fecid)->update(['fsisunataprobado' => 1]);

            //         }else{
            //             $encontrofecha = false;
            //             $fecid = 0;
            //         }
            //     }

            //     if($encontrofecha == true){

            //         $estadosunataprobado = 1;

            //         if($ex_estadocomprobante == "NO ACEPTADO" || $ex_estadocomprobante == "INCIDENCIAS" || $ex_estadocomprobante == "Incidencia" || $ex_estadocomprobante == "Rechazado"){
            //             $estadosunataprobado = 0;
            //         }

            //         $fsi = fsifacturassi::where('fsifactura', $ex_documentocomprobante)->first();

            //         if($fsi){
            //             $fsi->fsisunataprobado = $estadosunataprobado;
            //             $fsi->update();
            //         }else{
            //             $logs["NO_SE_ENCONTRO_DOCUMENTO"][] = "NO SE ENCONTRO EL DOCUMENTO: ".$ex_documentocomprobante." EN LA LINEA: ".$i;
            //         }

            //     }else{
            //         $respuesta = false;
            //         $mensaje  = "No se encontro la fecha seleccionada";
            //         $logs["NO_SE_ENCONTRO_FECHA"][] = "Fecha Mes: ".$ex_mes.", EN EL AÑO: ".$ex_anio." EN LA LINEA: ".$i;
            //         break;
            //     }
            // }

            // 

            // AGREGAR REGISTRO
            
            $fec = fecfechas::where('fecmesabierto', true)->first(['fecid']);
            $fecid = $fec->fecid;

            $espe = espestadospendientes::where('fecid', $fecid)
                                        ->where('espbasedato', "Operaciones Sunat")
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
                                        ->where('espbasedato', "Sell In (Factura Efectiva)")
                                        ->where('espfechactualizacion', '!=', null)
                                        ->first();

                    if($espcount){
                        $aree->areporcentaje = "100";
                    }else{
                        $aree->areporcentaje = "50";
                    }

                    $aree->update();
                }
            }

            

            $care = carcargasarchivos::find($carid);
            $care->carexito = 1;
            $care->update();

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
            'CARGAR DATA DE ESTADO SUNAT FACTURAS SI ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/si/estado-sunat-facturas', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }
}
