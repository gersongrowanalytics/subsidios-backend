<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos\SO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\usuusuarios;
use App\Models\fecfechas;
use App\Models\carcargasarchivos;

class MetCargarBultosNoReconocidosController extends Controller
{
    public function MetCargarBultosNoReconocidos(Request $request)
    {

        $obtenerPks = false;
        $enviarCorreos = true;
        $subirData = true;

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "NUMERO_LINEAS_EXCEL" => 0
        );

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $archivo  = $_FILES['file']['name'];
        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);

        $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

        $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/SO/BultosNoReconocidos/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);
        $fichero_subido = base_path().'/public'.$ubicacionArchivo;
        $ex_file_name = explode(".", $_FILES['file']['name']);

        if($obtenerPks == true){
            $carultimo = carcargasarchivos::orderby('carid', 'desc')->first();
            $pkcar = $carultimo->carid + 1;
        }

        $carn = new carcargasarchivos;
        if($obtenerPks == true){
            $carn->carid        = $pkcar;
        } 
        // $carn->tcaid        = 3;
        $carn->usuid        = $usu->usuid;
        $carn->carnombre    = $_FILES['file']['name'];
        $carn->carextension = $ex_file_name[1];
        $carn->carurl       = env('APP_URL').$ubicacionArchivo;
        $carn->carexito     = 0;
        $carn->save();

        if($obtenerPks == true){
            $carid = $pkcar;
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {


            if($enviarCorreos == true){
                $data = [
                    'archivo' => $_FILES['file']['name'], "tipo" => "Subsidios | Bultos No Reconocidos", "usuario" => $usu->usuusuario,
                    "url_archivo" => env('APP_URL').$ubicacionArchivo
                ];
                Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

                $data = [
                    'archivo' => $_FILES['file']['name'], "tipo" => "Subsidios | Bultos No Reconocidos", "usuario" => $usu->usuusuario,
                    "url_archivo" => env('APP_URL').$ubicacionArchivo
                ];
                Mail::to(env('jazmin.laguna@grow-analytics.com.pe'))->send(new MailCargaArchivoOutlook($data));
            }

            if($subirData == true){
                
                $objPHPExcel    = IOFactory::load($fichero_subido);
                $objPHPExcel->setActiveSheetIndex(0);
                $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

                $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

                $fecid = 0;

                for ($i=3; $i <= $numRows ; $i++) {

                    $ex_anio                = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                    $ex_mes                 = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                    $ex_destrucsap          = $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue();
                    $ex_bultosnoreconocidos = $objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue();
                    $ex_bultosacidos        = $objPHPExcel->getActiveSheet()->getCell('AD'.$i)->getCalculatedValue();
                    $ex_montoacido          = $objPHPExcel->getActiveSheet()->getCell('AE'.$i)->getCalculatedValue();

                    if($i == 3){
                        $fec = fecfechas::where('fecmesabreviacion', $ex_mes)
                                        ->where('fecanionumero', $ex_anio)
                                        ->first();

                        if($fec){
                            $fecid = $fec->fecid;
                        }else{
                            break;
                        }
                    }

                    if($fecid != 0){

                        $sdee = sdesubsidiosdetalles::where('fecid', $fecid)
                                                    ->where('sdedestrucsap', $ex_destrucsap)
                                                    ->first();

                        if($sdee){

                            $sdebultosacido = $sdee->sdecantidadbultosreal - $ex_bultosnoreconocidos;

                            $sdee->sdebultosnoreconocido = $ex_bultosnoreconocidos;
                            $sdee->sdebultosacido = $sdebultosacido;
                            $sdee->sdemontoacido = $sdebultosacido * $sdee->sdedsctodos;
                            $sdee->update();

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
            'CARGAR DATA DE SUBSIDIOS BULTOS NO RECONOCIDOS', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/so/subsidios/bultos-no-reconocidos', //audruta
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
