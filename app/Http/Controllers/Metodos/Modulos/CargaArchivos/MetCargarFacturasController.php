<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\PHPExcel_Shared_Date;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\usuusuarios;
use App\Models\tpctiposcomprobantes;
use App\Models\secseriescomprobantes;
use App\Models\cliclientes;
use App\Models\fadfacturasdetalles;
use App\Models\facfacturas;
use App\Models\ntcnotascreditos;
use App\Models\ncdnotascreditosdetalles;
use App\Models\proproductos;
use App\Models\fecfechas;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarFacturasController extends Controller
{
    public function CargarFacturas(Request $request)
    {
        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => 0,
            "CORRELATIVOS_FACTURAS_NO_ENCONTRADOS" => [],
            "NO_ES_FORMATO_CORRELATIVO_FACTURA" => [],
            "NO_ES_FORMATO_SERIE_FACTURA" => [],
            "NO_ES_FORMATO_CODIGO_FACTURA" => [],
            "CODIGO_DOCUMENTO_NO_EXISTE" => [],
            "TPC_NO_ENCONTRADO" => [],
            "NUEVA_SERIE_CREADA" => [],
            "NO_EXISTE_FACTURA_ASIGNADA" => [],
            "NO_GUARDO_FACTURA" => [],
            "NO_GUARDO_NOTA_CREDITO" => [],
            "FECHAS_NO_ENCONTRADAS" => [],
            "FECHAS_NO_CUMPLE_FORMATO" => [],
        );

        $pkis = array();

        $respuesta      = true;
        $mensaje        = "El archivo se subio correctamente";
        $datos          = [];
        $mensajeDetalle = "";


        // $usutoken = $request->header('api_token');
        $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        $archivo  = $_FILES['file']['name'];

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario']);

        $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

        $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/Facturas/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);
        $fichero_subido = base_path().'/public'.$ubicacionArchivo;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {

            $data = [
                'archivo' => $_FILES['file']['name'], "tipo" => "Cargar Facturas", "usuario" => $usu->usuusuario,
                "url_archivo" => env('APP_URL').$ubicacionArchivo
            ];
            Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

            $borrarFacturas     = false;
            $borrarNotasCredito = false;

            // OBTENER CODIGOS DE COMPROBANTES
            $tpcEstaticos = array(
                array(
                    "id" => 0,
                    "codigo" => "NO ES NINGUNO",
                )
            );

            // OBTENER SERIES DE COMPROBANTES
            $secEstaticos = array(
                array(
                    "id" => 0,
                    "tpcid" => "NO ES NINGUNO",
                    "serie" => "NO ES NINGUNO",
                )
            );
            

            $objPHPExcel    = IOFactory::load($fichero_subido);
            $objPHPExcel->setActiveSheetIndex(0);
            $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

            $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

            for ($i=2; $i <= $numRows ; $i++) {

                
                $fechaFactura              = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $fechaFactura = Date::excelToDateTimeObject($fechaFactura);
                $fechaFactura = json_encode($fechaFactura);
                $fechaFactura = json_decode($fechaFactura);
                $fechaFactura = date("Y-m-d", strtotime($fechaFactura->date));

                $canalDistribucion         = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                $facturaSap                = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                $facturaCorrelativo        = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                $asignacionNotaCredito     = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                $facturaAnulada            = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                $claseFactura              = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                $estadoFactura             = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
                $codigoSolicitante         = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                $codigoVendedorSolicitante = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
                $nombreVendedorSolicitante = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();
                $fechaFacturaOriginal      = $objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue();
                $codigoMaterial            = $objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue();
                $cantidadFactura           = $objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue();
                $unidadMedidaVenta         = $objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue();
                $valorNeto                 = $objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue();
                $importeImpuestos          = $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue();
                $valorTotal                = $objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue();
                $pesoNeto                  = $objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue();
                $unidadPeso                = $objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue();
                $porcentajeDevolucion      = $objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue();
                $cantidadFacturaUme        = $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue();
                $unidadMedidaBase          = $objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue();


                // VARIABLES PARA CREAR EL DOCUMENTO
                $fecidDocumento = 0;
                $facidAsignadoDocumento = 0;
                $cliidDocumento         = 0;
                $tpcidDocumento         = 0;
                $secidDocumento         = 0;
                $codigoCompletoDocumento = $facturaCorrelativo;
                $serieDocumento         = "";
                $correlativoDocumento   = "";
                $codigoDocumento        = "";
                $codigoSapDocumento     = $facturaSap;
                $subTotalDocumento      = $valorNeto;
                $impuestoDocumento      = $importeImpuestos;
                $totalDocumento         = $valorTotal;

                // $fechaFactura = Carbon::parse($fechaFactura)->format('Y-m-d');

                if(isset($fechaFactura)){
                    $arrayFecha = explode("-", $fechaFactura);

                    if(sizeof($arrayFecha) == 3){
                        $fecfechaFecha = $arrayFecha[2]."/".$arrayFecha[1]."/".$arrayFecha[0];

                        $fec = fecfechas::where('fecfecha', $fecfechaFecha)->first();

                        if($fec){
                            $fecidDocumento = $fec->fecid;

                            $correlativoFactura = explode("-", $facturaCorrelativo);

                            if(sizeof($correlativoFactura) == 3){

                                $codigoFactura  = $correlativoFactura[0];
                                $serieFactura   = $correlativoFactura[1];
                                $correlativoFac = $correlativoFactura[2];

                                if(strlen($codigoFactura) == 2){

                                    $codigoDocumento = $codigoFactura;

                                    $encontroTpc = false;

                                    foreach($tpcEstaticos as $tpcEstatico){
                                        if($tpcEstatico['codigo'] == $codigoFactura){

                                            $tpcidDocumento = $tpcEstatico['id'];

                                            $encontroTpc = true;
                                            break;
                                        }
                                    }

                                    if($encontroTpc == false){
                                        $tpc = tpctiposcomprobantes::where('tpccodigo', $codigoFactura)->first();
                                        if($tpc){
                                            $tpcEstaticos[] = array(
                                                "id" => $tpc->tpcid,
                                                "codigo" => $codigoFactura,
                                            );

                                            $tpcidDocumento = $tpc->tpcid;

                                            $encontroTpc = true;
                                        }else{
                                            $respuesta = false;
                                            $mensaje = "";
                                            $logs['CODIGO_DOCUMENTO_NO_EXISTE'][] = "El codigo del comprobante: ".$codigoFactura." no existe en los registros, porfavor revisar bien el codigo asignado. EN LA LINEA: ".$i;
                                        }
                                    }

                                    if($encontroTpc == true){

                                        if(strlen($serieFactura) == 4){

                                            $serieDocumento = $serieFactura;

                                            $encontroSec = false;

                                            foreach($secEstaticos as $secEstatico){
                                                if($secEstatico['serie'] == $serieFactura && $secEstatico['tpcid'] == $tpcidDocumento ){

                                                    $secidDocumento = $secEstatico['id'];

                                                    $encontroSec = true;
                                                    break;
                                                }
                                            }

                                            if($encontroSec == false){
                                                $sec = secseriescomprobantes::where('secserie', $serieDocumento )
                                                                            ->where('tpcid', $tpcidDocumento )
                                                                            ->first();
                                                if($sec){

                                                    $secEstaticos[] = array(
                                                        "id"    => $sec->secid,
                                                        "tpcid" => $tpcidDocumento,
                                                        "serie" => $serieDocumento,
                                                    );
                    
                                                    $secidDocumento = $sec->secid;
                    
                                                    $encontroSec = true;

                                                }else{
                                                    $secn = new secseriescomprobantes;
                                                    $secn->tpcid          = $tpcidDocumento;
                                                    $secn->secserie       = $serieDocumento;
                                                    $secn->secdescripcion = "NUEVA SERIE";
                                                    if($secn->save()){
                                                        $encontroSec = true;
                                                        $secidDocumento = $secn->secid;
                                                        $logs['NUEVA_SERIE_CREADA'][] = "La serie : ".$serieDocumento." se acaba de registrar en nuestros servidores. EN LA LINEA: ".$i;
                                                    }
                                                }
                                            }

                                            if($encontroSec == true){
                                                if(is_numeric ( $correlativoFac )){
                                                    $correlativoFac = intval($correlativoFac);

                                                    $correlativoDocumento = $correlativoFac;

                                                    $cli = cliclientes::where('clicodigo', $codigoSolicitante)->first();

                                                    if($cli){
                                                        $cliidDocumento = $cli->cliid;

                                                        if($tpcidDocumento == 1){

                                                            if($borrarFacturas == false){
                                                                // facfacturas::get()->delete();
                                                                fadfacturasdetalles::where('fadid', 'like', '%%')->delete();
                                                                facfacturas::where('facid', 'like', '%%')->delete();
                                                                $borrarFacturas = true;
                                                            }


                                                            $fac = facfacturas::where('facsap', $codigoSapDocumento)->first();
                                                            if($fac){

                                                                $pro = proproductos::where('prosku', $codigoMaterial)->first();

                                                                if($pro){
                                                                    $fadn = new fadfacturasdetalles;
                                                                    $fadn->fecid             = $fecidDocumento;
                                                                    $fadn->facid             = $fac->facid;
                                                                    $fadn->proid             = $pro->proid;
                                                                    $fadn->cliid             = $cliidDocumento;
                                                                    $fadn->fadcantidad       = $cantidadFactura;
                                                                    $fadn->fadpreciounitario = "0";
                                                                    $fadn->fadsubtotal       = $subTotalDocumento;
                                                                    $fadn->fadimpuesto       = $impuestoDocumento;
                                                                    $fadn->fadtotal          = $totalDocumento;
                                                                    if($fadn->save()){
                                                                        $fac->facsubtotal = $fac->facsubtotal + $subTotalDocumento;
                                                                        $fac->facimpuesto = $fac->facimpuesto + $impuestoDocumento;
                                                                        $fac->factotal    = $fac->factotal + $totalDocumento;
                                                                        $fac->update();
                                                                    }
                                                                }else{
                                                                    $respuesta = false;
                                                                    $mensaje = "No existe el producto: ".$codigoMaterial." recomendamos actualizar dicha maestra, linea excel: ".$i;
                                                                    $logs['NO_EXISTE_PRODUCTO'][] = "El producto: ".$codigoMaterial." no se encontro en nuestros registros, recomendamos actualizar la maestra de productos EN LA LINEA: ".$i;
                                                                }

                                                            }else{
                                                                $facn = new facfacturas;
                                                                $facn->fecid             = $fecidDocumento;
                                                                $facn->cliid             = $cliidDocumento;
                                                                $facn->tpcid             = $tpcidDocumento;
                                                                $facn->secid             = $secidDocumento;
                                                                $facn->faccodigocompleto = $codigoCompletoDocumento;
                                                                $facn->facserie          = $serieDocumento;
                                                                $facn->faccorrelativo    = $correlativoDocumento;
                                                                $facn->faccodigo         = $codigoDocumento;
                                                                $facn->facsap            = $codigoSapDocumento;
                                                                $facn->facsubtotal       = $subTotalDocumento;
                                                                $facn->facimpuesto       = $impuestoDocumento;
                                                                $facn->factotal          = $totalDocumento;
                                                                if($facn->save()){
                                                                    
                                                                    $pro = proproductos::where('prosku', $codigoMaterial)->first();

                                                                    if($pro){
                                                                        $fadn = new fadfacturasdetalles;
                                                                        $fadn->fecid             = $fecidDocumento;
                                                                        $fadn->facid             = $facn->facid;
                                                                        $fadn->proid             = $pro->proid;
                                                                        $fadn->cliid             = $cliidDocumento;
                                                                        $fadn->fadcantidad       = $cantidadFactura;
                                                                        $fadn->fadpreciounitario = "0";
                                                                        $fadn->fadsubtotal       = $subTotalDocumento;
                                                                        $fadn->fadimpuesto       = $impuestoDocumento;
                                                                        $fadn->fadtotal          = $totalDocumento;
                                                                        $fadn->save();
                                                                    }else{
                                                                        $respuesta = false;
                                                                        $mensaje = "No existe el producto: ".$codigoMaterial." recomendamos actualizar dicha maestra, linea excel: ".$i;
                                                                        $logs['NO_EXISTE_PRODUCTO'][] = "El producto: ".$codigoMaterial." no se encontro en nuestros registros, recomendamos actualizar la maestra de productos EN LA LINEA: ".$i;
                                                                    }

                                                                }else{
                                                                    $logs['NO_GUARDO_FACTURA'][] = "La factura: ".$codigoCompletoDocumento." no se pudo guardar en nuestros registros, revisar bien sus datos EN LA LINEA: ".$i;
                                                                }
                                                            }

                                                        }else if($tpcidDocumento == 3){

                                                            if($borrarNotasCredito == false){
                                                                // ntcnotascreditos::get()->delete();
                                                                ncdnotascreditosdetalles::where('ncdid', 'like', '%%')->delete();
                                                                ntcnotascreditos::where('ntcid', 'like', '%%')->delete();
                                                                $borrarNotasCredito = true;
                                                            }

                                                            $fac = facfacturas::where('faccodigocompleto', $correlativoFacturaAsignada)->first();

                                                            if($fac){
                                                                
                                                                $facidAsignadoDocumento = $fac->facid;

                                                            }else{
                                                                $facidAsignadoDocumento = null;
                                                                $logs['NO_EXISTE_FACTURA_ASIGNADA'][] = "La factura asignada:".$correlativoFacturaAsignada." para la nota de credito: ".$codigoCompletoDocumento."no existe en nuestros registros, recomendamos actualizar con la información de dicha factura. EN LA LINEA: ".$i;
                                                            }

                                                            $ntc = ntcnotascreditos::where('ntccodigocompleto', $codigoCompletoDocumento)->first();

                                                            if($ntc){

                                                                $pro = proproductos::where('prosku', $codigoMaterial)->first();

                                                                if($pro){
                                                                    $ncdn = new ncdnotascreditosdetalles;
                                                                    $ncdn->ntcid       = $ntc->ntcid;
                                                                    $ncdn->facid       = $facidAsignadoDocumento;
                                                                    $ncdn->cliid       = $cliidDocumento;
                                                                    $ncdn->proid       = $pro->proid;
                                                                    $ncdn->sdeid       = null; // OJO
                                                                    $ncdn->ncdcantidad = $cantidadFactura;
                                                                    $ncdn->ncdtotal    = $totalDocumento;
                                                                    if($ncdn->save()){
                                                                        $ntc->ntcsubtotal = $ntc->ntcsubtotal + $subTotalDocumento;
                                                                        $ntc->ntcimpuesto = $ntc->ntcimpuesto + $impuestoDocumento;
                                                                        $ntc->ntctotal    = $ntc->ntctotal + $totalDocumento;
                                                                        $ntc->update();
                                                                    }
                                                                }else{
                                                                    $respuesta = false;
                                                                    $mensaje = "No existe el producto: ".$codigoMaterial." recomendamos actualizar dicha maestra, linea excel: ".$i;
                                                                    $logs['NO_EXISTE_PRODUCTO'][] = "El producto: ".$codigoMaterial." no se encontro en nuestros registros, recomendamos actualizar la maestra de productos EN LA LINEA: ".$i;
                                                                }

                                                            }else{
                                                                $ntcn = new ntcnotascreditos;
                                                                $ntcn->facid              = $facidAsignadoDocumento;
                                                                $ntcn->cliid              = $cliidDocumento;
                                                                $ntcn->tpcid              = $tpcidDocumento;
                                                                $ntcn->secid              = $secidDocumento;
                                                                $ntcn->ntccodigocompleto  = $codigoCompletoDocumento;
                                                                $ntcn->ntcfacturaasignada = $correlativoFacturaAsignada;
                                                                $ntcn->ntcserie           = $serieDocumento;
                                                                $ntcn->ntccorrelativo     = $correlativoDocumento;
                                                                $ntcn->ntccodigo          = $codigoDocumento;
                                                                $ntcn->ntcsap             = $codigoSapDocumento;
                                                                $ntcn->ntcsubtotal        = $subTotalDocumento;
                                                                $ntcn->ntcimpuesto        = $impuestoDocumento;
                                                                $ntcn->ntctotal           = $totalDocumento;
                                                                if($ntcn->save()){

                                                                    $pro = proproductos::where('prosku', $codigoMaterial)->first();

                                                                    if($pro){
                                                                        $ncdn = new ncdnotascreditosdetalles;
                                                                        $ncdn->ntcid       = $ntcn->ntcid;
                                                                        $ncdn->facid       = $facidAsignadoDocumento;
                                                                        $ncdn->cliid       = $cliidDocumento;
                                                                        $ncdn->proid       = $pro->proid;
                                                                        $ncdn->sdeid       = null; // OJO
                                                                        $ncdn->ncdcantidad = $cantidadFactura;
                                                                        $ncdn->ncdtotal    = $totalDocumento;
                                                                        $ncdn->save();
                                                                    }else{
                                                                        $respuesta = false;
                                                                        $mensaje = "No existe el producto: ".$codigoMaterial." recomendamos actualizar dicha maestra, linea excel: ".$i;
                                                                        $logs['NO_EXISTE_PRODUCTO'][] = "El producto: ".$codigoMaterial." no se encontro en nuestros registros, recomendamos actualizar la maestra de productos EN LA LINEA: ".$i;
                                                                    }

                                                                }else{
                                                                    $logs['NO_GUARDO_NOTA_CREDITO'][] = "La nota de credito: ".$codigoCompletoDocumento." no se pudo guardar en nuestros registros, revisar bien sus datos EN LA LINEA: ".$i;
                                                                }
                                                            }

                                                        }else{
                                                            $respuesta = false;
                                                            $mensaje = "No existe el tipo de documento encontrado";
                                                        }
                                                    }else{
                                                        $respuesta = false;
                                                        $mensaje = "";
                                                        $logs['NO_EXISTE_CLIENTE'][] = "El codigo del cliente: ".$codigoSolicitante." no existe en nuestros registros, recomendamos actualizar la maestra de clientes. EN LA LINEA: ".$i;    
                                                    }

                                                }else{
                                                    $respuesta = false;
                                                    $mensaje = "";
                                                    $logs['NO_ES_FORMATO_CORRELATIVO_FACTURA'][] = $correlativoFac." EN LA LINEA: ".$i;
                                                }
                                            }


                                        }else{
                                            $respuesta = false;
                                            $mensaje = "";
                                            $logs['NO_ES_FORMATO_SERIE_FACTURA'][] = "La serie: ".$serieFactura." no tiene 4 digitos EN LA LINEA: ".$i;
                                        }

                                    }else{
                                        $respuesta = false;
                                        $mensaje = "";
                                        $logs['TPC_NO_ENCONTRADO'][] = "El tpc no se encontro en los filtros realizados "."EN LA LINEA: ".$i;
                                    }

                                }else{
                                    $respuesta = false;
                                    $mensaje = "";
                                    $logs['NO_ES_FORMATO_CODIGO_FACTURA'][] = "El codigo del comprobante: ".$codigoFactura." no tiene 2 digitos EN LA LINEA: ".$i;
                                }

                            }else{
                                $respuesta = false;
                                $mensaje = "";
                                $logs['CORRELATIVOS_FACTURAS_NO_ENCONTRADOS'][] = $facturaCorrelativo." EN LA LINEA: ".$i;
                            }
                        }else{
                            $respuesta = false;
                            $mensaje = "Lo sentimos, no encontramos algunas fechas registradas | EN LA LINEA: ".$i;        
                        }
                    }else{
                        $logs['FECHAS_NO_CUMPLE_FORMATO'][] = $fechaFactura." LINEA :".$i;
                        $respuesta = false;
                        $mensaje = "Lo sentimos, no se encontraron algunas fechas: ".$fechaFactura." LINEA :".$i;
                    }

                }else{
                    $logs['FECHAS_NO_ENCONTRADAS'][] = $fechaFactura." LINEA :".$i;
                    $respuesta = false;
                    $mensaje = "Lo sentimos, no se encontraron algunas fechas: ".$fechaFactura." LINEA :".$i;
                }
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
            'CARGAR DATA DE FACTURAS AL SISTEMA ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/facturas', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }
}
 