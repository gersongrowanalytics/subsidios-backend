<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos\SI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\fecfechas;
use App\Models\nsinotascreditossi;
use App\Models\usuusuarios;
use App\Models\tpctiposcomprobantes;
use App\Models\secseriescomprobantes;
use App\Models\cliclientes;
use App\Models\fadfacturasdetalles;
use App\Models\facfacturas;
use App\Models\ntcnotascreditos;
use App\Models\ncdnotascreditosdetalles;
use App\Models\proproductos;
use App\Models\fsifacturassi;
use App\Models\fdsfacturassidetalles;
use App\Models\ndsnotascreditossidetalles;
use App\Models\sfssubsidiosfacturassi;
use App\Models\espestadospendientes;
use App\Models\areareasestados;
use \DateTime;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

class MetCargarFacturasSiController extends Controller
{
    public function MetCargarFacturasSi(Request $request)
    {
        // @ini_set( 'upload_max_size' , '64M' );
        // @ini_set( 'post_max_size', '128M');
        // @ini_set( 'memory_limit', '256M' );
        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');
        $fecid = 0;
        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => 0,
            "FECHA_NO_REGISTRADA" => "",
            "PRODUCTOS_NO_ENCONTRADOS" => [],
            "FACTURA_NO_ASIGNADA" => [],
            "CORRELATIVOS_FACTURAS_NO_ENCONTRADOS" => [],
            "TPC_NO_ENCONTRADO" => [],
            "CLIENTES_NO_ENCONTRADOS" => [],
            "NO_ES_FORMATO_CORRELATIVO_FACTURA" => [],
            "NO_ES_FORMATO_SERIE_FACTURA" => [],
            "NO_ES_FORMATO_CODIGO_FACTURA" => [],
            "FECHAS_NO_CUMPLE_FORMATO" => [],
            "FACTURAS_ANULADAS" => [],
            "NO_GUARDO_FACTURA" => [],
            "NUEVA_SERIE_CREADA" => [],
            "CODIGO_DOCUMENTO_NO_EXISTE" => []
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

        $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/SI/Facturas/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);
        $fichero_subido = base_path().'/public'.$ubicacionArchivo;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {

            $data = [
                'archivo' => $_FILES['file']['name'], "tipo" => "Facturas SI", "usuario" => $usu->usuusuario,
                "url_archivo" => env('APP_URL').$ubicacionArchivo
            ];
            Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

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


            $secultimo = secseriescomprobantes::orderby('secid', 'desc')->first();
            $pksec = $secultimo->secid + 1;

            $fdsultimo = fdsfacturassidetalles::orderby('fdsid', 'desc')->first();
            $pkfds = $fdsultimo->fdsid + 1;
            
            $fsiultimo = fsifacturassi::orderby('fsiid', 'desc')->first();
            $pkfsi = $fsiultimo->fsiid + 1;
            
            
            $ndsultimo = ndsnotascreditossidetalles::orderby('ndsid', 'desc')->first();
            $pknds = $ndsultimo->ndsid + 1;

            $nsiultimo = nsinotascreditossi::orderby('nsiid', 'desc')->first();
            $pknsi = $nsiultimo->nsiid + 1;


            for ($i=2; $i <= $numRows; $i++) {

                $ex_anio      = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                $ex_mes       = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();

                $ex_solicitante      = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                $ex_destinatario     = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                $ex_material         = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                $ex_moneda           = "-";
                $ex_clase            = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                $ex_fechafactura     = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                $ex_facturasap       = "-";
                $ex_factura          = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();

                $ex_valorneto        = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                $ex_valornetodolares = "0";
                $ex_pedido           = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
                $ex_pedidooriginal   = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();
                $ex_facturaanulada   = $objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue();

                $pos = strpos($ex_facturaanulada, "X");

                if($ex_facturaanulada != "X"){
                // if($pos !== false){
                    $encontroFecha = false;

                    $arrayFecha = explode(".", $ex_fechafactura);

                    if(sizeof($arrayFecha) == 3){
                        $encontroFecha = true;
                    }else{
                        $arrayFecha = explode("/", $ex_fechafactura);
                        if(sizeof($arrayFecha) == 3){
                            $encontroFecha = true;
                        }else{

                            // $fechaFactura              = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                            $ex_fechafactura = Date::excelToDateTimeObject($ex_fechafactura);
                            $ex_fechafactura = json_encode($ex_fechafactura);
                            $ex_fechafactura = json_decode($ex_fechafactura);
                            $ex_fechafactura = date("d-m-Y", strtotime($ex_fechafactura->date));

                            $arrayFecha = explode("-", $ex_fechafactura);
                            
                            if(sizeof($arrayFecha) == 3){
                                $encontroFecha = true;
                            }else{
                                $encontroFecha = false;
                            }
                        }
                    }

                    if($encontroFecha == true){
                        $fecfechaFecha = $arrayFecha[2]."-".$arrayFecha[1]."-01";

                        $fec = fecfechas::where('fecfecha', "$fecfechaFecha")->first();

                        if($fec){

                            if($i == 2){

                                $fecid = $fec->fecid;

                                ndsnotascreditossidetalles::where('fecid', $fec->fecid)->delete();
                                nsinotascreditossi::where('fecid', $fec->fecid)->delete();
                                fdsfacturassidetalles::where('fecid', $fec->fecid)->delete();
                                fsifacturassi::where('fecid', $fec->fecid)->delete();
                                sfssubsidiosfacturassi::where('fecid', $fec->fecid)->delete();

                            }

                            $fecidDocumento = $fec->fecid;

                            $correlativoFactura = explode("-", $ex_factura);

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
                                            // $logs['CODIGO_DOCUMENTO_NO_EXISTE'][] = "El codigo del comprobante: ".$codigoFactura." no existe en los registros, porfavor revisar bien el codigo asignado. EN LA LINEA: ".$i;
                                            $logs['CODIGO_DOCUMENTO_NO_EXISTE'] = $this->EliminarDuplicidad( $logs["CODIGO_DOCUMENTO_NO_EXISTE"], $codigoFactura, $i);
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
                                                    $secn->secid = $pksec;
                                                    $secn->tpcid          = $tpcidDocumento;
                                                    $secn->secserie       = $serieDocumento;
                                                    $secn->secdescripcion = "NUEVA SERIE";
                                                    if($secn->save()){
                                                        $pksec = $pksec + 1;
                                                        $encontroSec = true;
                                                        $secidDocumento = $secn->secid;
                                                        // $logs['NUEVA_SERIE_CREADA'][] = "La serie : ".$serieDocumento." se acaba de registrar en nuestros servidores. EN LA LINEA: ".$i;
                                                        $logs['NUEVA_SERIE_CREADA'] = $this->EliminarDuplicidad( $logs["NUEVA_SERIE_CREADA"], $serieDocumento, $i);
                                                    }
                                                }
                                            }


                                            if($encontroSec == true){
                                                if(is_numeric ( $correlativoFac )){

                                                    $correlativoFac = intval($correlativoFac);

                                                    $correlativoDocumento = $correlativoFac;

                                                    $cli = cliclientes::where('clicodigoshipto', $ex_destinatario)->first();

                                                    if($cli){

                                                        $cliidDocumento = $cli->cliid;

                                                        if($tpcidDocumento == 1){ // FACTURA

                                                            if($i == 2){
                                                                // ndsnotascreditossidetalles::where('fecid', $fec->fecid)->delete();
                                                                // nsinotascreditossi::where('fecid', $fec->fecid)->delete();
                                                                // fdsfacturassidetalles::where('fecid', $fec->fecid)->delete();
                                                                // fsifacturassi::where('fecid', $fec->fecid)->delete();
                                                            }

                                                            $fsi = fsifacturassi::where('fsifactura', $ex_factura)->first();

                                                            if($fsi){

                                                                $pro = proproductos::where('prosku', $ex_material)->first();

                                                                if($pro){

                                                                    $fdsn = new fdsfacturassidetalles;
                                                                    $fdsn->fdsid = $pkfds;
                                                                    $fdsn->fecid = $fecidDocumento;
                                                                    $fdsn->fsiid = $fsi->fsiid;
                                                                    $fdsn->proid = $pro->proid;
                                                                    $fdsn->cliid = $cliidDocumento;
                                                                    $fdsn->fdsmaterial  = $ex_material;
                                                                    $fdsn->fdsmoneda    = $ex_moneda;
                                                                    $fdsn->fdsvalorneto = $ex_valorneto;
                                                                    $fdsn->fdsvalornetodolares = $ex_valornetodolares;
                                                                    $fdsn->fdspedido         = $ex_pedido;
                                                                    $fdsn->fdspedidooriginal = $ex_pedidooriginal;

                                                                    $fdsn->fdstreintaporciento = ($ex_valorneto*30)/100;
                                                                    $fdsn->fdssaldo            = ($ex_valorneto*30)/100;;
                                                                    $fdsn->fdsreconocer        = 0;
                                                                    

                                                                    if($fdsn->save()){
                                                                        $pkfds = $pkfds + 1;
                                                                        $fsi->fsivalorneto = $fsi->fsivalorneto + $ex_valorneto;
                                                                        $fsi->fsivalornetodolares = $fsi->fsivalornetodolares + $ex_valornetodolares;
                                                                        $fsi->update();
                                                                    }

                                                                }else{
                                                                    $respuesta = false;
                                                                    $mensaje = "No existe el producto: ".$ex_material." recomendamos actualizar dicha maestra, linea excel: ".$i;
                                                                    // $logs['NO_EXISTE_PRODUCTO'][] = "El producto: ".$ex_material." no se encontro en nuestros registros, recomendamos actualizar la maestra de productos EN LA LINEA: ".$i;
                                                                    $logs['PRODUCTOS_NO_ENCONTRADOS'] = $this->EliminarDuplicidad( $logs["PRODUCTOS_NO_ENCONTRADOS"], $ex_material, $i);
                                                                }

                                                            }else{
                                                                $fsin = new fsifacturassi;
                                                                $fsin->fsiid = $pkfsi;
                                                                $fsin->fecid = $fecidDocumento;
                                                                $fsin->cliid = $cliidDocumento;
                                                                $fsin->tpcid = $tpcidDocumento;
                                                                $fsin->secid = $secidDocumento;
                                                                $fsin->fsisolicitante  = $ex_solicitante;
                                                                $fsin->fsidestinatario = $ex_destinatario;
                                                                $fsin->fsimoneda       = $ex_moneda;
                                                                $fsin->fsiclase        = $ex_clase;
                                                                $fsin->fsifecha        = $ex_fechafactura;
                                                                $fsin->fsisap          = $ex_facturasap;
                                                                $fsin->fsifactura      = $ex_factura;
                                                                $fsin->fsivalorneto    = $ex_valorneto;
                                                                $fsin->fsivalornetodolares = $ex_valornetodolares;
                                                                $fsin->fsipedido         = $ex_pedido;
                                                                $fsin->fsipedidooriginal = $ex_pedidooriginal;
                                                                if($fsin->save()){
                                                                    $pkfsi = $pkfsi + 1;
                                                                    $pro = proproductos::where('prosku', $ex_material)->first();

                                                                    if($pro){
                                                                        $fdsn = new fdsfacturassidetalles;
                                                                        $fdsn->fdsid = $pkfds;
                                                                        $fdsn->fecid = $fecidDocumento;
                                                                        $fdsn->fsiid = $fsin->fsiid;
                                                                        $fdsn->proid = $pro->proid;
                                                                        $fdsn->cliid = $cliidDocumento;
                                                                        $fdsn->fdsmaterial  = $ex_material;
                                                                        $fdsn->fdsmoneda    = $ex_moneda;
                                                                        $fdsn->fdsvalorneto = $ex_valorneto;
                                                                        $fdsn->fdsvalornetodolares = $ex_valornetodolares;
                                                                        $fdsn->fdspedido         = $ex_pedido;
                                                                        $fdsn->fdspedidooriginal = $ex_pedidooriginal;

                                                                        $fdsn->fdstreintaporciento = ($ex_valorneto*30)/100;
                                                                        $fdsn->fdssaldo            = ($ex_valorneto*30)/100;;
                                                                        $fdsn->fdsreconocer        = 0;
                                                                        $fdsn->save();

                                                                        $pkfds = $pkfds + 1;

                                                                    }else{
                                                                        $respuesta = false;
                                                                        $mensaje = "No existe el producto: ".$ex_material." recomendamos actualizar dicha maestra, linea excel: ".$i;
                                                                        // $logs['NO_EXISTE_PRODUCTO'][] = "El producto: ".$ex_material." no se encontro en nuestros registros, recomendamos actualizar la maestra de productos EN LA LINEA: ".$i;
                                                                        $logs['PRODUCTOS_NO_ENCONTRADOS'] = $this->EliminarDuplicidad( $logs["PRODUCTOS_NO_ENCONTRADOS"], $ex_material, $i);
                                                                    }

                                                                }else{
                                                                    // $logs['NO_GUARDO_FACTURA'][] = "La factura: ".$ex_factura." no se pudo guardar en nuestros registros, revisar bien sus datos EN LA LINEA: ".$i;
                                                                    $logs['NO_GUARDO_FACTURA'] = $this->EliminarDuplicidad( $logs["NO_GUARDO_FACTURA"], $ex_factura, $i);
                                                                }

                                                            }

                                                        }else if($tpcidDocumento == 3){ // NOTA DE CREDITO

                                                            if($i == 2){
                                                                // ndsnotascreditossidetalles::where('fecid', $fec->fecid)->delete();
                                                                // nsinotascreditossi::where('fecid', $fec->fecid)->delete();
                                                            }

                                                            $nsi = nsinotascreditossi::where('nsifecha', $ex_fechafactura)->first();

                                                            if($nsi){

                                                                $fsi = fsifacturassi::where('fsipedido', $ex_pedidooriginal)->first();

                                                                if($fsi){
                                                                    $pro = proproductos::where('prosku', $ex_material)->first();

                                                                    if($pro){
                                                                        $ndsn = new ndsnotascreditossidetalles;
                                                                        $ndsn->ndsid = $pknds;
                                                                        $ndsn->fecid = $fecidDocumento;
                                                                        $ndsn->nsiid = $nsi->nsiid;
                                                                        $ndsn->fsiid = $fsi->fsiid;
                                                                        $ndsn->proid = $pro->proid;
                                                                        $ndsn->cliid = $cliidDocumento;
                                                                        $ndsn->ndsmaterial    = $ex_material;
                                                                        $ndsn->ndsclase       = $ex_clase;
                                                                        $ndsn->ndsnotacredito = $ex_factura;
                                                                        $ndsn->ndsvalorneto   = $ex_valorneto;
                                                                        $ndsn->ndsvalornetodolares = $ex_valornetodolares;
                                                                        $ndsn->ndspedido      = $ex_pedido;
                                                                        $ndsn->ndspedidooriginal = $ex_pedidooriginal;
                                                                        if($ndsn->save()){
                                                                            $pknds = $pknds + 1;
                                                                            $nsi->nsivalorneto = $nsi->nsivalorneto + $ex_valorneto;
                                                                            $nsi->nsivalornetodolares = $nsi->nsivalornetodolares + $ex_valornetodolares;
                                                                            $nsi->update();

                                                                            // $fds = fdsfacturassidetalles::where('fsiid', $fsi->fsiid)
                                                                            //                             ->where('proid', $pro->proid)
                                                                            //                             ->first();

                                                                            // if($fds){
                                                                            //     $nuevoSaldo = $fds->fdssaldo - $ex_valorneto;
                                                                            //     if($nuevoSaldo >= 0){
                                                                            //         $fds->fdssaldo     = $nuevoSaldo;
                                                                            //         $fds->fdsreconocer = $fds->fdsreconocer + $ex_valorneto;
                                                                            //         $fds->save();
                                                                            //     }else{
                                                                            //         $fds->fdssaldo       = $nuevoSaldo;
                                                                            //         $fds->fdsreconocer   = $fds->fdsreconocer + $ex_valorneto;
                                                                            //         $fds->fdsobservacion = true;
                                                                            //         $fds->save();

                                                                            //         $logs["OBSERVACIONES_NOTAS_CREDITO_FACTURAS_DETALLADAS"][] = "SE ENCONTRO UN DETALLE DE LA FACTURA DONDE EL SALDO ES MENOR A 0, PEDIDO ORIGINAL: ".$ex_pedidooriginal." CON EL NUEVO SALDO DE: ".$nuevoSaldo." EN LA LINEA".$i;
                                                                            //     }

                                                                            // }else{
                                                                            //     $respuesta = false;
                                                                            //     $mensaje = "No se encontro el detalle de una factura asignada pedido original: ".$ex_pedidooriginal." para el producto: ".$ex_material." recomendamos actualizar la información de facturas, linea excel: ".$i;
                                                                            //     $logs['NO_SE_ENCONTRO_DETALLE_FACTURA_ASIGNADA'][] = "No se encontro el detalle de una factura asignada con el pedido original: ".$ex_pedidooriginal." para el producto: ".$ex_material." recomendamos actualizar la información de facturas, linea excel: ".$i;
                                                                            // }
                                                                        }

                                                                    }else{
                                                                        $respuesta = false;
                                                                        $mensaje = "No existe el producto: ".$ex_material." recomendamos actualizar dicha maestra, linea excel: ".$i;
                                                                        // $logs['NO_EXISTE_PRODUCTO'][] = "El producto: ".$ex_material." no se encontro en nuestros registros, recomendamos actualizar la maestra de productos EN LA LINEA: ".$i;
                                                                        $logs['PRODUCTOS_NO_ENCONTRADOS'] = $logs['PRODUCTOS_NO_ENCONTRADOS'][] = $this->EliminarDuplicidad( $logs["PRODUCTOS_NO_ENCONTRADOS"], $ex_material, $i);
                                                                    }
                                                                }else{
                                                                    $respuesta = false;
                                                                    $mensaje = "No se encontro una factura asignada a esta nota de credito";
                                                                    // $logs['FACTURA_NO_ASIGNADA'][] = "El pedido original: ".$ex_pedidooriginal." no existe en nuestros registros, recomendamos actualizar la información de facturas. EN LA LINEA: ".$i;
                                                                    $logs['FACTURA_NO_ASIGNADA'] = $this->EliminarDuplicidad( $logs["FACTURA_NO_ASIGNADA"], $ex_pedidooriginal, $i);
                                                                }

                                                            }else{
                                                                $nsin = new nsinotascreditossi;
                                                                $nsin->nsiid = $pknsi;
                                                                $nsin->fecid = $fecidDocumento;
                                                                $nsin->tpcid = $tpcidDocumento;
                                                                $nsin->secid = $secidDocumento;
                                                                $nsin->nsimoneda = $ex_moneda;
                                                                $nsin->nsiclase  = $ex_clase;
                                                                $nsin->nsifecha  = $ex_fechafactura;
                                                                $nsin->nsisap    = $ex_facturasap;
                                                                $nsin->nsinotacredito = $ex_factura;
                                                                $nsin->nsivalorneto   = $ex_valorneto;
                                                                $nsin->nsivalornetodolares = $ex_valornetodolares;
                                                                if($nsin->save()){
                                                                    $pknsi = $pknsi + 1;
                                                                    $fsi = fsifacturassi::where('fsipedido', $ex_pedidooriginal)->first();

                                                                    if($fsi){
                                                                        
                                                                        $pro = proproductos::where('prosku', $ex_material)->first();

                                                                        if($pro){
                                                                            $ndsn = new ndsnotascreditossidetalles;
                                                                            $ndsn->ndsid = $pknds;
                                                                            $ndsn->fecid = $fecidDocumento;
                                                                            $ndsn->nsiid = $nsin->nsiid;
                                                                            $ndsn->fsiid = $fsi->fsiid;
                                                                            $ndsn->proid = $pro->proid;
                                                                            $ndsn->cliid = $cliidDocumento;
                                                                            $ndsn->ndsmaterial    = $ex_material;
                                                                            $ndsn->ndsclase       = $ex_clase;
                                                                            $ndsn->ndsnotacredito = $ex_factura;
                                                                            $ndsn->ndsvalorneto   = $ex_valorneto;
                                                                            $ndsn->ndsvalornetodolares = $ex_valornetodolares;
                                                                            $ndsn->ndspedido      = $ex_pedido;
                                                                            $ndsn->ndspedidooriginal = $ex_pedidooriginal;
                                                                            if($ndsn->save()){
                                                                                $pknds = $pknds + 1;
                                                                                // $fds = fdsfacturassidetalles::where('fsiid', $fsi->fsiid)
                                                                                //                             ->where('proid', $pro->proid)
                                                                                //                             ->first();

                                                                                // if($fds){
                                                                                //     $nuevoSaldo = $fds->fdssaldo - $ex_valorneto;
                                                                                //     if($nuevoSaldo >= 0){
                                                                                //         $fds->fdssaldo     = $nuevoSaldo;
                                                                                //         $fds->fdsreconocer = $fds->fdsreconocer + $ex_valorneto;
                                                                                //         $fds->save();
                                                                                //     }else{
                                                                                //         $fds->fdssaldo       = $nuevoSaldo;
                                                                                //         $fds->fdsreconocer   = $fds->fdsreconocer + $ex_valorneto;
                                                                                //         $fds->fdsobservacion = true;
                                                                                //         $fds->save();

                                                                                //         $logs["OBSERVACIONES_NOTAS_CREDITO_FACTURAS_DETALLADAS"][] = "SE ENCONTRO UN DETALLE DE LA FACTURA DONDE EL SALDO ES MENOR A 0, PEDIDO ORIGINAL: ".$ex_pedidooriginal." CON EL NUEVO SALDO DE: ".$nuevoSaldo." EN LA LINEA".$i;
                                                                                //     }

                                                                                // }else{
                                                                                //     $respuesta = false;
                                                                                //     $mensaje = "No se encontro el detalle de una factura asignada pedido original: ".$ex_pedidooriginal." para el producto: ".$ex_material." recomendamos actualizar la información de facturas, linea excel: ".$i;
                                                                                //     $logs['NO_SE_ENCONTRO_DETALLE_FACTURA_ASIGNADA'][] = "No se encontro el detalle de una factura asignada con el pedido original: ".$ex_pedidooriginal." para el producto: ".$ex_material." recomendamos actualizar la información de facturas, linea excel: ".$i;
                                                                                // }
                                                                            }
                                                                        }else{
                                                                            $respuesta = false;
                                                                            $mensaje = "No existe el producto: ".$ex_material." recomendamos actualizar dicha maestra, linea excel: ".$i;
                                                                            // $logs['NO_EXISTE_PRODUCTO'][] = "El producto: ".$ex_material." no se encontro en nuestros registros, recomendamos actualizar la maestra de productos EN LA LINEA: ".$i;
                                                                            $logs['PRODUCTOS_NO_ENCONTRADOS'] = $logs['PRODUCTOS_NO_ENCONTRADOS'][] = $this->EliminarDuplicidad( $logs["PRODUCTOS_NO_ENCONTRADOS"], $ex_material, $i);
                                                                        }

                                                                    }else{
                                                                        $respuesta = false;
                                                                        $mensaje = "No se encontro una factura asignada a esta nota de credito";
                                                                        // $logs['FACTURA_NO_ASIGNADA'][] = "El pedido original: ".$ex_pedidooriginal." no existe en nuestros registros, recomendamos actualizar la información de facturas. EN LA LINEA: ".$i;
                                                                        $logs['FACTURA_NO_ASIGNADA'] = $this->EliminarDuplicidad( $logs["FACTURA_NO_ASIGNADA"], $ex_pedidooriginal, $i);
                                                                    }
                                                                }
                                                            }

                                                        }else{
                                                            $respuesta = false;
                                                            $mensaje = "No existe el tipo de documento encontrado";
                                                        }

                                                    }else{
                                                        $respuesta = false;
                                                        $mensaje = "";
                                                        // $logs['CLIENTES_NO_ENCONTRADOS'][] = "El codigo del cliente: ".$ex_solicitante." no existe en nuestros registros, recomendamos actualizar la maestra de clientes. EN LA LINEA: ".$i;    
                                                        $logs["CLIENTES_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["CLIENTES_NO_ENCONTRADOS"], $ex_solicitante, $i);
                                                    }

                                                }else{
                                                    $respuesta = false;
                                                    $mensaje = "";
                                                    // $logs['NO_ES_FORMATO_CORRELATIVO_FACTURA'][] = $correlativoFac." EN LA LINEA: ".$i;
                                                    $logs["NO_ES_FORMATO_CORRELATIVO_FACTURA"] = $this->EliminarDuplicidad( $logs["NO_ES_FORMATO_CORRELATIVO_FACTURA"], $correlativoFac, $i);
                                                }
                                            }

                                        }else{
                                            $respuesta = false;
                                            $mensaje = "";
                                            // $logs['NO_ES_FORMATO_SERIE_FACTURA'][] = "La serie: ".$serieFactura." no tiene 4 digitos EN LA LINEA: ".$i;
                                            $logs["NO_ES_FORMATO_SERIE_FACTURA"] = $this->EliminarDuplicidad( $logs["NO_ES_FORMATO_SERIE_FACTURA"], $serieFactura, $i);
                                        }

                                    }else{
                                        $respuesta = false;
                                        $mensaje = "";
                                        // $logs['TPC_NO_ENCONTRADO'][] = "El tpc no se encontro en los filtros realizados "."EN LA LINEA: ".$i;
                                        $logs["TPC_NO_ENCONTRADO"] = $this->EliminarDuplicidad( $logs["TPC_NO_ENCONTRADO"], $codigoFactura, $i);
                                    }

                                }else{
                                    $respuesta = false;
                                    $mensaje = "";
                                    // $logs['NO_ES_FORMATO_CODIGO_FACTURA'][] = "El codigo del comprobante: ".$codigoFactura." no tiene 2 digitos EN LA LINEA: ".$i;
                                    $logs["NO_ES_FORMATO_CODIGO_FACTURA"] = $this->EliminarDuplicidad( $logs["NO_ES_FORMATO_CODIGO_FACTURA"], $codigoFactura, $i);
                                }

                            }else{
                                $respuesta = false;
                                $mensaje = "";
                                $logs["CORRELATIVOS_FACTURAS_NO_ENCONTRADOS"] = $this->EliminarDuplicidad( $logs["CORRELATIVOS_FACTURAS_NO_ENCONTRADOS"], $ex_factura, $i);
                            }

                        }else{
                            $respuesta = false;
                            $mensaje = "Lo sentimos, no encontramos algunas fechas registradas | EN LA LINEA: ".$i;
                            break;
                        }
                    }else{
                        // $logs['FECHAS_NO_CUMPLE_FORMATO'][] = $ex_fechafactura." LINEA :".$i;
                        $logs["FECHAS_NO_CUMPLE_FORMATO"] = $this->EliminarDuplicidad( $logs["FECHAS_NO_CUMPLE_FORMATO"], $ex_fechafactura, $i);
                        $respuesta = false;
                        $mensaje = "Lo sentimos, no se encontraron algunas fechas: ".$ex_fechafactura." LINEA :".$i;
                        break;
                    }
                }else{
                    // $logs['FACTURAS_ANULADAS'][] = "Factura Anulada : ".$ex_facturaanulada." se acaba de encontrar. EN LA LINEA: ".$i;
                    $logs["FACTURAS_ANULADAS"] = $this->EliminarDuplicidad( $logs["FACTURAS_ANULADAS"], $ex_facturaanulada, $i);
                }

            }

            // 

            // AGREGAR REGISTRO
            
            $fec = fecfechas::where('fecmesabierto', true)->first(['fecid']);
            $fecid = $fec->fecid;

            $espe = espestadospendientes::where('fecid', $fecid)
                                        ->where('espbasedato', "Sell In (Factura Efectiva)")
                                        ->first();

            if($espe){
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
                    
                    $espcount = espestadospendientes::where('fecid', $fec->fecid)
                                        ->where('espbasedato', "Sell In (Factura Efectiva)")
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

            //

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
            'CARGAR DATA DE FACTURAS SI ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/si/facturas', //audruta
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
