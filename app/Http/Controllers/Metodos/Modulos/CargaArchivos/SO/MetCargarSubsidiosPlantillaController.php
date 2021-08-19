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
use App\Models\sfssubsidiosfacturassi;
use App\Models\espestadospendientes;
use App\Models\areareasestados;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use \DateTime;

class MetCargarSubsidiosPlantillaController extends Controller
{
    public function MetCargarSubsidiosPlantilla(Request $request)
    {

        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

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

        $fichero_subido = base_path().'/public/Sistema/Modulos/CargaArchivos/SO/SubsidiosPlantilla/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {

            $objPHPExcel    = IOFactory::load($fichero_subido);
            $objPHPExcel->setActiveSheetIndex(0);
            $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

            $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

            // $ex_anio = $objPHPExcel->getActiveSheet()->getCell('A2')->getCalculatedValue();
            // $ex_mes  = $objPHPExcel->getActiveSheet()->getCell('B3')->getCalculatedValue();

            $ex_anio = $objPHPExcel->getActiveSheet()->getCell('C2')->getCalculatedValue();
            $ex_mes  = $objPHPExcel->getActiveSheet()->getCell('C3')->getCalculatedValue();
            
            $fec = fecfechas::where('fecmesabreviacion', $ex_mes)
                            ->where('fecanionumero', $ex_anio)
                            ->where('fecdianumero', "1")
                            ->first(['fecid', 'fecmesabierto']);

            if($fec){

                // CERRAR TODOS LOS MESES Y ABRIR SOLO EL ACTUAL
                // if($fec->fecmesabierto != true){

                //     $fece = fecfechas::where('fecmesabierto', true)->first();
                //     $fece->fecmesabierto = false;
                //     $fece->update();

                //     $fec->fecmesabierto = true;
                //     $fec->update();
                // }
                
                sdesubsidiosdetalles::where('fecid', $fec->fecid)
                                    // ->where('sdesac', 0)
                                    ->update(["sdeeditado" => 0]);
                
                $pksde = 49880;


                for ($i=6; $i <= $numRows ; $i++) {
                    $pksde = $pksde + 1;

                    // $ex_anio                = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                    // $ex_mes                 = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();

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

                    //  ---------------
                    
                    $ex_cantidadbultos      = $objPHPExcel->getActiveSheet()->getCell('Y'.$i)->getCalculatedValue();
                    $ex_montoareconocer     = $objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue();

                    // CAMPOS OTORGADOS POR SAC
                    $ex_cantidadbultosreal  = $objPHPExcel->getActiveSheet()->getCell('AA'.$i)->getCalculatedValue();
                    $ex_montoareconocerreal = $objPHPExcel->getActiveSheet()->getCell('AB'.$i)->getCalculatedValue();

                    $ex_status              = $objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue();
                    $ex_diferenciaahorrocliente = $objPHPExcel->getActiveSheet()->getCell('AD'.$i)->getCalculatedValue();
    
                    
    
                    $pro = proproductos::where('prosku', $ex_codigouni)->first(['proid']);
    
                    if($pro){
                        $cli = cliclientes::where('clicodigoshipto', $ex_codigodestinatario)->first(['cliid', 'cliclientesac']);
    
                        if($cli){
                            
                            // $sdee = sdesubsidiosdetalles::where('fecid', $fec->fecid)
                            //                         ->where('sdedestrucsap', $ex_destrucsap)
                            //                         ->first();
                            $sdee = false;
                            if($sdee){

                                $sdee->fecid = $fec->fecid;
                                $sdee->proid = $pro->proid;
                                $sdee->cliid = $cli->cliid;

                                $sdee->sdezona = $ex_zona;
                                $sdee->sdeterritorio = $ex_territorio;
                                $sdee->sdecliente = $ex_cliente;

                                $sdee->sdecodigosolicitante     = $ex_codigosolicitante;
                                $sdee->sdecodigodestinatario    = $ex_codigodestinatario;

                                $sdee->sdesectoruno             = $ex_sectoruno;

                                $sdee->sdesegmentoscliente      = $ex_segmentocliente;
                                $sdee->sdesubsegmentoscliente   = $ex_subsegmentocliente;
                                $sdee->sderucsubcliente         = $ex_rucsubcliente;
                                $sdee->sdesubcliente            = $ex_subcliente;
                                $sdee->sdenombrecomercial       = $ex_nombrecomercial;
                                $sdee->sdesector                = $ex_sector;
                                $sdee->sdecodigounitario        = $ex_codigouni;
                                $sdee->sdedescripcion           = $ex_descripcion;
                                $sdee->sdepcsapfinal            = $ex_pcsapfinal;
                                $sdee->sdedscto                 = $ex_dsctouno;
                                $sdee->sdepcsubsidiado          = $ex_pcsubsidiado;
                                $sdee->sdemup                   = $ex_mup;
                                $sdee->sdepvpigv                = $ex_pvpigv;
                                $sdee->sdedsctodos              = $ex_dsctodos;
                                $sdee->sdedestrucsap            = $ex_destrucsap;
                                $sdee->sdeinicio                = $ex_inicio;
                                $sdee->sdebultosacordados       = $ex_bultosacordados;
                                
                                if($cli->cliclientesac == 1){
                                    $sdee->sdesac = true;

                                }else{
                                    $sdee->sdesac = false;
                                }

                                $sdee->sdeeditado = 1;
                                $sdee->update();

                            }else{



                                $sden = new sdesubsidiosdetalles;
                                $sden->sdeid = $pksde;
                                $sden->fecid = $fec->fecid;
                                $sden->proid = $pro->proid;
                                $sden->cliid = $cli->cliid;

                                $sden->sdezona = $ex_zona;
                                $sden->sdeterritorio = $ex_territorio;
                                $sden->sdecliente = $ex_cliente;

                                $sden->sdecodigosolicitante     = $ex_codigosolicitante;
                                $sden->sdecodigodestinatario    = $ex_codigodestinatario;

                                $sden->sdesectoruno             = $ex_sectoruno;

                                $sden->sdesegmentoscliente      = $ex_segmentocliente;
                                $sden->sdesubsegmentoscliente   = $ex_subsegmentocliente;
                                $sden->sderucsubcliente         = $ex_rucsubcliente;
                                $sden->sdesubcliente            = $ex_subcliente;
                                $sden->sdenombrecomercial       = $ex_nombrecomercial;
                                $sden->sdesector                = $ex_sector;
                                $sden->sdecodigounitario        = $ex_codigouni;
                                $sden->sdedescripcion           = $ex_descripcion;
                                $sden->sdepcsapfinal            = $ex_pcsapfinal;
                                $sden->sdedscto                 = $ex_dsctouno;
                                $sden->sdepcsubsidiado          = $ex_pcsubsidiado;
                                $sden->sdemup                   = $ex_mup;
                                $sden->sdepvpigv                = $ex_pvpigv;
                                $sden->sdedsctodos              = $ex_dsctodos;
                                $sden->sdedestrucsap            = $ex_destrucsap;
                                $sden->sdeinicio                = $ex_inicio;
                                $sden->sdebultosacordados       = $ex_bultosacordados;
                                
                                if($cli->cliclientesac == 1){
                                    $sden->sdesac = true;

                                }else{
                                    $sden->sdesac = false;
                                }



                                // PARAMETROS UTILIZADOS PARA CARGAR DATA HISTORICA
                                if($ex_cantidadbultos){

                                    if(is_numeric($ex_cantidadbultos)){
                                        $sden->sdecantidadbultos  = $ex_cantidadbultos;
                                        $sden->sdemontoareconocer = $ex_cantidadbultos * $sden->sdedsctodos;
                                    }else{
                                        $sden->sdecantidadbultos  = 0;
                                        $sden->sdemontoareconocer = 0;    
                                    }

                                }else{
                                    $sden->sdecantidadbultos  = 0;
                                    $sden->sdemontoareconocer = 0;
                                }

                                $sden->sdeaprobado = true;

                                if($ex_cantidadbultosreal){

                                    if(is_numeric($ex_cantidadbultosreal)){
                                        $sden->sdecantidadbultosreal  = $ex_cantidadbultosreal;
                                        $sden->sdemontoareconocerreal = $ex_cantidadbultosreal * $sden->sdedsctodos;
                                    }else{
                                        $sden->sdecantidadbultosreal  = 0;
                                        $sden->sdemontoareconocerreal = 0;
                                    }

                                }else{
                                    $sden->sdecantidadbultosreal  = 0;
                                    $sden->sdemontoareconocerreal = 0;
                                }

                                if($ex_cantidadbultos == $ex_cantidadbultosreal){
                                    $sden->sdestatus = "OK";
                                }else{
                                    $sden->sdestatus = "ERROR CANTIDADES";
                                }

                                $sden->sdediferenciaahorro = $ex_diferenciaahorrocliente;

                                //FINAL PARAMETROS UTILIZADOS PARA CARGAR DATA HISTORICA



                                $sden->save();
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
    
                }


                // LIMPIAR INFORMACIÓN 

                sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                        ->where('sde.fecid', $fec->fecid)
                                        ->where('sdeeditado', 0)
                                        ->delete();

                sdesubsidiosdetalles::where('fecid', $fec->fecid)
                                    ->where('sdeeditado', 0)
                                    ->delete();


                // AGREGAR REGISTRO

                $espe = espestadospendientes::where('fecid', $fec->fecid)
                                            ->where('espbasedato', "Subsidio Aprobado (Plantilla)")
                                            ->first();

                if($espe){
                    $espe->espfechactualizacion = $fechaActual;

                    $date1 = new DateTime($fechaActual);
                    $fecha_carga_real = date("Y-m-d", strtotime($espe->espfechaprogramado));
                    $date2 = new DateTime($fecha_carga_real);

                    $diff = $date2->diff($date1);
                    // $diff = $date1->diff($date2);

                    if($diff->days > 0){
                        $espe->espdiaretraso = $diff->days;
                    }else{
                        $espe->espdiaretraso = "0";
                    }

                    $espe->update();


                    $aree = areareasestados::where('areid', $espe->areid)->first();

                    if($aree){

                        $espcount = espestadospendientes::where('fecid', $fec->fecid)
                                            ->where('espbasedato', "Subsidio Aprobado (Plantilla)")
                                            ->where('espfechactualizacion', '!=', null)
                                            ->count();

                        if($espcount == 1){
                            $aree->areporcentaje = "50";
                        }else{
                            $aree->areporcentaje = "100";
                        }

                        $aree->update();
                    }
                }


            }else{
                $respuesta = false;
                $mensaje = "Lo sentimos, el mes o año asignado no se encuentra en los registros, recomendamos actualizar la maestra de fechas e intentar nuevamente gracias";
                $logs["FECHA_NO_REGISTRADA"] = "AÑO: ".$ex_anio." MES: ".$ex_mes;
            }

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
            'CARGAR DATA DE PLANTILLAS SUBSIDIOS SO', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/so/subsidios-so-plantilla', //audruta
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
