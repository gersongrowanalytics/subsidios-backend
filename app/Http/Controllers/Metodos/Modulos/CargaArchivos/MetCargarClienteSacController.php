<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\usuusuarios;
use App\Models\cliclientes;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarClienteSacController extends Controller
{
    public function MetCargarClienteSac(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => "0",
            "CLIENTE_NO_ENCONTRADO" => array(),
            "CLIENTE_ENCONTRADO" => array(),
            "CLIENTES_SAC" => array()
        );

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "El archivo se subio correctamente";
        $datos          = [];
        $mensajeDetalle = "";
        $mensajedev     = "";

        try{

            // $usutoken = $request->header('api_token');
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
            $archivo  = $_FILES['file']['name'];

            $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

            $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

            $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/ClientesSac/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);
            $fichero_subido = base_path().'/public'.$ubicacionArchivo;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {
                $data = [
                    'archivo' => $_FILES['file']['name'], "tipo" => "Clientes SAC", "usuario" => $usu->usuusuario,
                    "url_archivo" => env('APP_URL').$ubicacionArchivo
                ];
                Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

                $objPHPExcel    = IOFactory::load($fichero_subido);
                $objPHPExcel->setActiveSheetIndex(0);
                $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

                $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

                for ($i=2; $i <= $numRows ; $i++) {
                    $ex_zona                = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                    $ex_territorio          = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                    $ex_cliente             = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                    $ex_codigosolicitante   = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                    $ex_codigodestinatario  = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                    $ex_medioenvio          = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();

                    $cli = cliclientes::where('clicodigoshipto', $ex_codigodestinatario)->first();

                    if($cli){
                        $essac = false;

                        if($ex_medioenvio == "MANUAL" ){
                            $essac = true;
                            $logs["CLIENTES_SAC"][] = $ex_codigodestinatario;
                        }

                        $cli->cliclientesac = $essac;
                        if($cli->update()){
                            $logs["CLIENTE_ENCONTRADO"][] = $ex_codigodestinatario;
                        }

                        $pkis[] = $cli->cliid;

                    }else{
                        $logs["CLIENTE_NO_ENCONTRADO"][] = $ex_codigodestinatario;
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

        $requestsalida = response()->json([
            "respuesta"      => $respuesta,
            "mensaje"        => $mensaje,
            "datos"          => $datos,
            "mensajeDetalle" => $mensajeDetalle,
            "mensajedev"     => $mensajedev,
            "logs"           => $logs,
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
