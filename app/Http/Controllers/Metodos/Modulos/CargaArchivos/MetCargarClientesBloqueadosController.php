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
use App\Models\carcargasarchivos;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarClientesBloqueadosController extends Controller
{
    public function MetCargarClientesBloqueados(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => "0",
            "SUCURSAL_EDITADO"    => array(),
            "SUCURSAL_NO_EDITADO" => array(),
            "NUEVOS_SUCURSAL" => []
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

            $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

            $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

            $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/Clientes/Bloqueados/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);
            $fichero_subido = base_path().'/public'.$ubicacionArchivo;

            $ex_file_name = explode(".", $_FILES['file']['name']);

            $carultimo = carcargasarchivos::orderby('carid', 'desc')->first();
            $pkcar = $carultimo->carid + 1;

            if($usu->usuid != 1){
                $carn = new carcargasarchivos;
                $carn->carid        = $pkcar;
                $carn->tcaid        = 10;
                $carn->usuid        = $usu->usuid;
                $carn->carnombre    = $_FILES['file']['name'];
                $carn->carextension = $ex_file_name[1];
                $carn->carurl       = env('APP_URL').$ubicacionArchivo;
                $carn->carexito     = 0;
                $carn->save();
                $carid = $pkcar;
            }

            if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {
                
                if($usu->usuid != 1){
                    $data = [
                        'archivo' => $_FILES['file']['name'], "tipo" => "Maestra de Clientes Bloqueados", "usuario" => $usu->usuusuario,
                        "url_archivo" => env('APP_URL').$ubicacionArchivo
                    ];
                    Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));
                }

                if($usu->usuid == 1){

                    cliclientes::where('cliid', '>', 0)->update(['clibloqueado' => 0]);

                    $objPHPExcel    = IOFactory::load($fichero_subido);
                    $objPHPExcel->setActiveSheetIndex(0);
                    $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                    $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

                    $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

                    for ($i=2; $i <= $numRows ; $i++) {

                        $ex_codigo    = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                        $ex_bloqueado = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();

                        $bloqueado = false;

                        $pos = strpos($ex_bloqueado, "1");
                        if($pos !== false){
                            $bloqueado = true;
                        }
                        
                        $cli = cliclientes::where('clicodigoshipto', $ex_codigo)->first();

                        if($cli){
                            $cli->clibloqueado = $bloqueado;
                            $cli->update();
                        }
                        

                    }
                }

                if($usu->usuid != 1){
                    $care = carcargasarchivos::find($carid);
                    $care->carexito = 1;
                    $care->update();
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
            'CARGAR DATA DE CLIENTES BLOQUEADOS AL SISTEMA ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/clientes-bloqueados', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }
}
