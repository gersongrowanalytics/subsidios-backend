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

class MetCargarSubsidiosSacController extends Controller
{
    public function MetCargarSubsidiosSac(Request $request)
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
        $mensaje        = "El archivo se subio correctamente";
        $datos          = [];
        $mensajeDetalle = "";

        // $usutoken = $request->header('api_token');
        $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        $reiniciartodo  = $request['reiniciartodo'];
        $archivo  = $_FILES['file']['name'];

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

        $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

        $fichero_subido = base_path().'/public/Sistema/Modulos/CargaArchivos/SO/Subsidios/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {

            $objPHPExcel    = IOFactory::load($fichero_subido);
            $objPHPExcel->setActiveSheetIndex(0);
            $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

            $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

            $ex_anio = $objPHPExcel->getActiveSheet()->getCell('C2')->getCalculatedValue();
            $ex_mes  = $objPHPExcel->getActiveSheet()->getCell('C3')->getCalculatedValue();


            
            $fec = fecfechas::where('fecmesabreviacion', $ex_mes)
                            ->where('fecanionumero', $ex_anio)
                            ->first(['fecid']);

            if($fec){
                
                sdesubsidiosdetalles::where('fecid', $fec->fecid)
                                    // ->where('sdesac', 1)
                                    ->delete();

                for ($i=5; $i <= $numRows ; $i++) {
                    $ex_zona                = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                    $ex_territorio          = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                    $ex_cliente             = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                    $ex_codigosolicitante   = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                    $ex_codigodestinatario  = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();

                    $ex_sectoruno           = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();

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
                    $ex_bultosacordados     = $objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue();
                    
                    // CAMPOS OTORGADOS POR SAC
                    $ex_cantidadbultos      = $objPHPExcel->getActiveSheet()->getCell('Y'.$i)->getCalculatedValue();
                    $ex_montoareconocer     = $objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue();

                    
                    // $ex_cantidadbultosreal  = $objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue();
                    // $ex_montoareconocerreal = $objPHPExcel->getActiveSheet()->getCell('AA'.$i)->getCalculatedValue();

                    // $ex_status              = $objPHPExcel->getActiveSheet()->getCell('AB'.$i)->getCalculatedValue();
                    // $ex_diferenciaahorrocliente = $objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue();
    
    
    
                    $pro = proproductos::where('prosku', $ex_codigouni)->first(['proid']);
    
                    if($pro){
                        $cli = cliclientes::where('clicodigoshipto', $ex_codigodestinatario)->first(['cliid']);
    
                        if($cli){
    
                            $sden = new sdesubsidiosdetalles;
                            $sden->fecid = $fec->fecid;
                            $sden->proid = $pro->proid;
                            $sden->cliid = $cli->cliid;
                            $sden->sdecodigosolicitante     = $ex_codigosolicitante;
                            $sden->sdecodigodestinatario    = $ex_codigodestinatario;

                            $sden->sdesectoruno    = $ex_sectoruno;

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
                            
                            $sden->sdecantidadbultos        = $ex_cantidadbultos;
                            $sden->sdemontoareconocer       = $ex_montoareconocer;

                            // $sacStatus = false;
                            if($ex_cantidadbultos){
                                if($ex_cantidadbultos > 0){
                                    $sden->sdesac = true;
                                    $sden->sdeaprobado = true;
                                    $sden->sdecantidadbultosreal    = $ex_cantidadbultos;
                                    $sden->sdemontoareconocerreal   = $ex_montoareconocer;
                                }
                            }

                            // $sden->sdesac = true;
                            // $sden->sdeaprobado = true;



                            // $sden->sdestatus                = $ex_status;
                            // $sden->sdediferenciaahorro      = $ex_diferenciaahorrocliente;
                            $sden->save();
    
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
}
