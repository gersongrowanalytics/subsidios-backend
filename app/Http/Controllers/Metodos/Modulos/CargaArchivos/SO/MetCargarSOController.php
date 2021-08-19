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
use App\Models\fsofacturasso;
use App\Models\sdesubsidiosdetalles;
use App\Models\areareasestados;
use App\Models\espestadospendientes;
use \DateTime;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MetCargarSOController extends Controller
{
    public function MetCargarSO(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $fecid = 0;

        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => 0,
            "FECHA_NO_REGISTRADA" => "",
            "CLIENTES_NO_ENCONTRADOS" => [],
            "PRODUCTOS_NO_ENCONTRADOS" => []
        );

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "";
        $datos          = [];
        $mensajeDetalle = "";

        // $usutoken = $request->header('api_token');
        $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        $reiniciartodo  = $request['reiniciartodo'];
        $archivo  = $_FILES['file']['name'];

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

        $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

        $fichero_subido = base_path().'/public/Sistema/Modulos/CargaArchivos/SO/SO/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {
            $objPHPExcel    = IOFactory::load($fichero_subido);
            $objPHPExcel->setActiveSheetIndex(0);
            $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

            $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

            // fsofacturasso::where('fsoid', '>', '0')->delete();

            // for ($i=2; $i <= $numRows ; $i++) {
            //     $ex_codigo              = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
            //     // $ex_codigodistribuidor  = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
            //     $ex_codigofecha     = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
            //     $ex_codigocliente   = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
            //     $ex_codigoproducto  = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
            //     $ex_ruc             = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
            //     $ex_cantidadbultos  = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
            //     $ex_ventasinigv     = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();

            //     $ex_codigofecha = Date::excelToDateTimeObject($ex_codigofecha);
            //     $ex_codigofecha = json_encode($ex_codigofecha);
            //     $ex_codigofecha = json_decode($ex_codigofecha);
            //     $ex_codigofecha = date("Y-m", strtotime($ex_codigofecha->date));

            //     $fec = fecfechas::where('fecfecha', 'LIKE', "%".$ex_codigofecha."%")
            //                 ->first(['fecid']);
                
            //     if($fec){

            //         if($i == 2){
            //             fsofacturasso::where('fsoid', $fec->fecid)->delete();
            //             $fecid = $fec->fecid;
            //         }

            //         $pro = proproductos::where('prosku', $ex_codigoproducto)->first(['proid']);

            //         if($pro){

            //             $cli = cliclientes::where('clicodigoshipto', $ex_codigo)->first(['cliid']);

            //             if($cli){

            //                 $fson = new fsofacturasso;
            //                 $fson->fecid            = $fec->fecid;
            //                 $fson->cliid            = $cli->cliid;
            //                 $fson->proid            = $pro->proid;
            //                 $fson->fsoruc           = $ex_ruc;
            //                 $fson->fsocantidadbulto = $ex_cantidadbultos;
            //                 if($ex_ventasinigv){
            //                     $fson->fsoventasinigv   = $ex_ventasinigv;
            //                 }else{
            //                     $fson->fsoventasinigv   = 0;
            //                 }
            //                 $fson->save();

            //             }else{
            //                 $respuesta = false;
            //                 $mensaje = "Lo sentimos, hubieron algunos codigos de solicitante que no se encontraron registrados, recomendamos actualizar la maestra de clientes e intentar nuevamente gracias.";
            //                 $logs["CLIENTES_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["CLIENTES_NO_ENCONTRADOS"], $ex_codigo, $i);
            //             }

            //         }else{
            //             $respuesta = false;
            //             $mensaje = "Lo sentimos, hubieron algunos skus que no se encontraron registrados, recomendamos actualizar la maestra de productos e intentar nuevamente gracias.";
            //             $logs["PRODUCTOS_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["PRODUCTOS_NO_ENCONTRADOS"], $ex_codigoproducto, $i);
            //         } 

            //     }else{
            //         $respuesta = false;
            //         $mensaje = "Lo sentimos, el mes o año asignado no se encuentra en los registros, recomendamos actualizar la maestra de fechas e intentar nuevamente gracias";
            //         $logs["FECHA_NO_REGISTRADA"] = "FECHA: ".$ex_codigofecha;
            //         break;
            //     }
            // }


            // 

            // AGREGAR REGISTRO

            $fec = fecfechas::where('fecmesabierto', true)->first(['fecid']);
            $fecid = $fec->fecid;
            
            $espe = espestadospendientes::where('fecid', $fecid)
                                        ->where('espbasedato', "Sell Out (Efectivo)")
                                        ->first();

            if($espe){
                $espe->espfechactualizacion = $fechaActual;

                $date1 = new DateTime($fechaActual);
                $fecha_carga_real = date("Y-m-d", strtotime($espe->espfechaprogramado));
                $date2 = new DateTime($fecha_carga_real);

                $diff = $date1->diff($date2);

                if($diff->days > 0){
                    $espe->espdiaretraso = $diff->days;
                }else{
                    $espe->espdiaretraso = "0";
                }

                $espe->update();


                $aree = areareasestados::where('areid', $espe->areid)-first();
                if($aree){
                    if($aree->areporcentaje == "50"){
                        $aree->areporcentaje = "100";
                    }else if($aree->areporcentaje == "100"){
                        $aree->areporcentaje = "100";
                    }else{
                        $aree->areporcentaje = "50";
                    }
                }
            }

            // 
            

            // $this->Alinear();

        }else{
            $respuesta = false;
            $mensaje = "Lo sentimos, el archivo no se pudo guardar en el sistema";
        }

        $logs["MENSAJE"] = $mensaje;

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
            'CARGAR DATA DE SUBSIDIOS NO APROBADOS ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/so/subsidios-no-aprobados', //audruta
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

    public function Alinear($fecid)
    {

        // $fecid = 1005;

        // $fso = fsofacturasso::where('fecid', $fecid)->get();

        $sdes = sdesubsidiosdetalles::where('fecid', $fecid)
                                    ->where('sdesac', false)
                                    ->get();

        foreach($sdes as $sde){
            $fso = fsofacturasso::where('fecid', $sde->fecid)
                                ->where('cliid', $sde->cliid)
                                ->where('proid', $sde->proid)
                                ->where('fsoruc', $sde->sderucsubcliente)
                                ->first();

            if($fso){

                $fsosuma = fsofacturasso::where('fecid', $sde->fecid)
                                ->where('cliid', $sde->cliid)
                                ->where('proid', $sde->proid)
                                ->where('fsoruc', $sde->sderucsubcliente)
                                ->sum('fsocantidadbulto');

                $montoAReconocerReal = 0;

                if(floatval($fsosuma) > floatval($sde->sdecantidadbultos)){
                    $montoAReconocerReal = floatval($sde->sdecantidadbultos);
                }else{
                    $montoAReconocerReal = floatval($fsosuma);
                }

                $status = "OK";

                // if($fsosuma == $sde->sdecantidadbultos){
                if($fsosuma == $sde->sdebultosacordados){

                    // if($sde->sdemontoareconocer == $fso->fsoventasinigv){

                    // }else{
                    //     $status = "ERROR PRECIO";
                    // }

                }else{
                    $status = "ERROR CANTIDADES";
                }

                $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                $sdee->sdecantidadbultosreal = $montoAReconocerReal;
                $sdee->sdemontoareconocerreal = floatval($montoAReconocerReal) * floatval($sde->sdedsctodos);
                $sdee->sdestatus = $status;
                $sdee->sdeaprobado = true;

                $sdee->update();
                
                
            }
        
        }


    }

}
