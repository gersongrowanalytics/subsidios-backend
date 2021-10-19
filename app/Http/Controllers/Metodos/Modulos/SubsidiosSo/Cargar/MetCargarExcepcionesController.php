<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSo\Cargar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\usuusuarios;
use App\Models\fecfechas;
use App\Models\carcargasarchivos;
use App\Models\sdesubsidiosdetalles;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarExcepcionesController extends Controller
{
    public function MetCargarExcepciones(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $pkis = array();
        
        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => "",
            "FECHA_NO_REGISTRADA" => "",
            "SUBSIDIOS_NO_ENCONTRADOS" => []
        );

        $aplicarLogica = true;
        $enviarCorreo = false;

        $respuesta      = true;
        $mensaje        = "El archivo se subio correctamente";
        $datos          = [];
        $mensajeDetalle = "";
        $mensajedev     = "";

        try{

            $usutoken = $request->header('api_token');
            if(!isset($usutoken)){
                $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
            }

            $archivo  = $_FILES['file']['name'];

            $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

            $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

            $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/SO/SubsidiosExcepciones/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);;
            $fichero_subido = base_path().'/public'.$ubicacionArchivo;

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

            if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {

                if($enviarCorreo == true){
                    $data = [
                        'archivo' => $_FILES['file']['name'], 
                        "tipo" => "Subsidios SO Excepciones", 
                        "usuario" => $usu->usuusuario,
                        "url_archivo" => env('APP_URL').$ubicacionArchivo
                    ];
                    Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));
                }

                if($aplicarLogica){
                    $objPHPExcel    = IOFactory::load($fichero_subido);
                    $objPHPExcel->setActiveSheetIndex(0);
                    $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                    $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

                    $logs['NUMERO_LINEAS_EXCEL'] = $numRows;
                    
                    $fecid = 0;

                    for ($i=3; $i <= $numRows ; $i++) {
                        $ex_anio = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                        $ex_mes  = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                        $ex_destrucsap = $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue();
                        $ex_bultosnoreonocidos = $objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue();
                        

                        if($i == 3){
                            $fec = fecfechas::where('fecmesabreviacion', $ex_mes)
                                            ->where('fecanionumero', $ex_anio)
                                            ->first(['fecid']);

                            if($fec){
                                $fecid = $fec->fecid;
                            }
                        }

                        if($fecid != 0){
                            $sdee = sdesubsidiosdetalles::where('fecid', $fec->fecid)
                                                        ->where('sdedestrucsap', $ex_destrucsap)
                                                        ->first();

                            if($sdee){

                                if($ex_bultosnoreonocidos){
                                    if(is_numeric($ex_bultosnoreonocidos)){

                                        if($ex_bultosnoreonocidos != 0){
                                            $bultosAcidos = $sdee->sdecantidadbultosreal - $ex_bultosnoreonocidos;

                                            $sdee->sdebultosnoreconocido = $ex_bultosnoreonocidos;
                                            $sdee->sdebultosacido = $bultosAcidos;
                                            $sdee->sdemontoacido  = $bultosAcidos * $sdee->sdedsctodos;
                                            $sdee->update();
                                        }
                                    }
                                }

                            }else{
                                $respuesta = false;
                                $mensaje = "Lo sentimos, hubieron algunos subsidios que no se encontraron en la plantilla";
                                $logs["SUBSIDIOS_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["SUBSIDIOS_NO_ENCONTRADOS"], $ex_destrucsap, $i);
                            }

                        }else{
                            $respuesta = false;
                            $mensaje = "Lo sentimos, el mes o año asignado no se encuentra habilitado, recomendamos actualizar e intentar nuevamente gracias";
                            $logs["FECHA_NO_REGISTRADA"] = "AÑO: ".$ex_anio." MES: ".$ex_mes;
                        }


                    }

                    $care = carcargasarchivos::find($carid);
                    $care->carexito = 1;
                    $care->update();

                }

            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, el archivo no se pudo guardar en el sistema";
            }

        }catch (Exception $e) {
            $mensajedev = $e->getMessage();
            $respuesta  = false;
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
            'CARGAR DATA DE EXCEPCIONES SUBSIDIOS SO ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/subsidiosSo/cargar/excepciones', //audruta
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
