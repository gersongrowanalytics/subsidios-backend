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
use App\Models\cbucostosbultos;
use App\Models\proproductos;
use App\Models\tictipocambios;
use \DateTime;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarCostoXBultoController extends Controller
{
    public function MetCargarCostoXBulto(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "El archivo se subio correctamente";
        $datos          = [];
        $mensajeDetalle = "";
        $mensajedev = "";

        $logs = array(
            "PRODUCTOS_NO_ENCONTRADOS" => []
        );

        try{

            $usutoken = $request->header('api_token');
            if(!isset($usutoken)){
                $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
            }
            $archivo  = $_FILES['file']['name'];

            $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);

            $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

            $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/FormatoVentas/CostoXBultos/'.basename($codigoArchivoAleatorio.'-'.$fechaActual.'-'.$_FILES['file']['name']);
            $fichero_subido = base_path().'/public'.$ubicacionArchivo;


            if($usu->usuid != 1){

                $ex_file_name = explode(".", $_FILES['file']['name']);
                
                $carn = new carcargasarchivos;
                $carn->tcaid        = 14;
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
                        'archivo' => $_FILES['file']['name'], "tipo" => "Costos de Productos", "usuario" => $usu->usuusuario,
                        "url_archivo" => env('APP_URL').$ubicacionArchivo
                    ];
                    Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

                    $data = [
                        'archivo' => $_FILES['file']['name'], "tipo" => "Costos de Productos", "usuario" => $usu->usuusuario,
                        "url_archivo" => env('APP_URL').$ubicacionArchivo
                    ];
                    Mail::to('jazmin.laguna@grow-analytics.com.pe')->send(new MailCargaArchivoOutlook($data));
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

                        $agregarCbu = true;

                        $fechasEditadas = [];

                        for ($i=2; $i <= $numRows ; $i++) {

                            $ex_anio        = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                            $ex_mes         = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                            $ex_sku         = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                            $ex_descsku     = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                            $ex_directo     = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                            $ex_indirecto   = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                            $ex_total       = $ex_indirecto + $ex_indirecto;
                            $ex_total_dolares = 0;

                            // if($i == 2){

                            $fec = fecfechas::where('fecmesnumero', $ex_mes)
                                            ->where('fecanionumero', $ex_anio)
                                            ->first();

                            if($fec){

                                $eliminarData = true;

                                foreach($fechasEditadas as $fechaEditada){
                                    if($fechaEditada == $fec->fecid){
                                        $eliminarData = false;
                                    }
                                }

                                if($eliminarData == true){

                                    $fechasEditadas[] = $fec->fecid;
                                    cbucostosbultos::where('fecid', $fec->fecid)->delete();

                                }
                                
                                $tic = tictipocambios::where('fecid', $fec->fecid)->first();
                                if($tic){
                                    $ex_total_dolares = $ex_total / $tic->tictc;
                                }else{
                                    $logs["TIPO_CAMBIO_NO_REGISTRADO"] = "EL TIPO DE CAMBIO NO SE HA REGISTRADO EN LA FECHA SELECCIONADA: ".$ex_anio." ".$ex_mes;
                                }

                            }else{
                                $agregarCbu = false;
                                $respuesta = false;
                                $logs["FECHA_NO_REGISTRADA"] = $ex_anio." ".$ex_mes." EN LA LINEA: ".$i;
                            }
                            // }

                            

                            if($agregarCbu == true){

                                if(isset($ex_sku)){
                                    $pro = proproductos::where('prosku', $ex_sku)->first();

                                    if($pro){

                                        $cbun = new cbucostosbultos;
                                        $cbun->fecid        = $fec->fecid;
                                        $cbun->proid        = $pro->proid;
                                        $cbun->cbusku       = $ex_sku;
                                        $cbun->cbudescsku   = $ex_descsku;
                                        if(isset($ex_directo)){
                                            $cbun->cbudirecto   = $ex_directo;
                                        }

                                        if(isset($ex_indirecto)){
                                            $cbun->cbuindirecto = $ex_indirecto;
                                        }

                                        if(isset($ex_total)){
                                            $cbun->cbutotal     = $ex_total;    
                                        }
                                        
                                        if(isset($ex_total_dolares)){
                                            $cbun->cbutotaldolares = $ex_total_dolares;    
                                        }
                                        
                                        $cbun->save();

                                    }else{

                                        $cbun = new cbucostosbultos;
                                        $cbun->fecid        = $fec->fecid;
                                        // $cbun->proid        = 0;
                                        $cbun->cbusku       = $ex_sku;
                                        $cbun->cbudescsku   = $ex_descsku;
                                        if(isset($ex_directo)){
                                            $cbun->cbudirecto   = $ex_directo;
                                        }

                                        if(isset($ex_indirecto)){
                                            $cbun->cbuindirecto = $ex_indirecto;
                                        }

                                        if(isset($ex_total)){
                                            $cbun->cbutotal     = $ex_total;    
                                        }
                                        
                                        if(isset($ex_total_dolares)){
                                            $cbun->cbutotaldolares = $ex_total_dolares;    
                                        }

                                        $cbun->save();

                                        $respuesta = false;
                                        $mensaje = "Lo sentimos, hubieron algunos skus que no se encontraron registrados, recomendamos actualizar la maestra de productos e intentar nuevamente gracias.";
                                        $logs["PRODUCTOS_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["PRODUCTOS_NO_ENCONTRADOS"], $ex_sku, $i);

                                    }
                                }

                            }
 


                        }

                    }


                    // REGISTRAR ACTUALIZACION EN EL HOME
                    if($usu->usuid != 1){

                        $care = carcargasarchivos::find($carid);
                        $care->carexito = 1;
                        $care->update();
    
                        $espe = espestadospendientes::where('fecid', $fecid)
                                                    ->where('espbasedato', "Costos de Productos")
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
            'CARGAR DATA DE COSTOS DE PRODUCTOS', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/costo-x-bulto', //audruta
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
