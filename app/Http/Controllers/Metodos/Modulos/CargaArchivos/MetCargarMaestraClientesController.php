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
use App\Models\espestadospendientes;
use App\Models\areareasestados;
use App\Models\fecfechas;
use \DateTime;

class MetCargarMaestraClientesController extends Controller
{
    public function CargarMaestraClientes(Request $request)
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

            $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);

            $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();

            $ubicacionArchivo = '/Sistema/Modulos/CargaArchivos/Clientes/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);
            $fichero_subido = base_path().'/public'.$ubicacionArchivo;

            $ex_file_name = explode(".", $_FILES['file']['name']);

            if($usu->usuid != 1){
                $carultimo = carcargasarchivos::orderby('carid', 'desc')->first();
                $pkcar = $carultimo->carid + 1;

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
                    // $data = [
                    //     'archivo' => $_FILES['file']['name'], "tipo" => "Maestra de Clientes", "usuario" => $usu->usuusuario,
                    //     "url_archivo" => env('APP_URL').$ubicacionArchivo
                    // ];
                    // Mail::to(env('USUARIO_ENVIAR_MAIL'))->send(new MailCargaArchivoOutlook($data));

                    $data = [
                        'archivo' => $_FILES['file']['name'], "tipo" => "Maestra de Clientes", "usuario" => $usu->usuusuario,
                        "url_archivo" => env('APP_URL').$ubicacionArchivo
                    ];
                    Mail::to('jazmin.laguna@grow-analytics.com.pe', 'milagros.roncal@grow-analytics.com.pe', 'gerson.vilca@grow-analytics.com.pe')->send(new MailCargaArchivoOutlook($data));
                }

                if($usu->usuid == 1){

                    $objPHPExcel    = IOFactory::load($fichero_subido);
                    $objPHPExcel->setActiveSheetIndex(0);
                    $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                    $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

                    $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

                    for ($i=2; $i <= $numRows ; $i++) {

                        // $codShipTo      = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                        // $shipTo         = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                        // $codSoldTo      = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                        // $soldTo         = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                        // $clienteHml     = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                        // $sucHml         = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                        // $departamento   = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                        // $grupoHml       = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
                        // $tv             = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                        // $zona           = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
                        // $region         = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();
                        // $canal          = $objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue();
                        // $tipoAtencion   = $objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue();
                        // $canalAtencion  = $objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue();
                        // $segmentoClienteFinal  = $objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue();
                        // $subSegmento      = $objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue();
                        // $segmentoRegional = $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue();
                        // $gerenteRegional  = $objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue();
                        // $gerenteZona      = $objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue();
                        // $ejecutivo        = $objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue();
                        // $identificador    = $objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue();


                        $codShipTo      = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                        $shipTo         = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                        $codSoldTo      = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                        $soldTo         = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
                        $clienteHml     = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();
                        $sucHml         = $objPHPExcel->getActiveSheet()->getCell('L'.$i)->getCalculatedValue();
                        $departamento   = $objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue();  
                        $grupoHml       = $objPHPExcel->getActiveSheet()->getCell('AG'.$i)->getCalculatedValue(); // 
                        $tv             = $objPHPExcel->getActiveSheet()->getCell('N'.$i)->getCalculatedValue();
                        $zona           = $objPHPExcel->getActiveSheet()->getCell('O'.$i)->getCalculatedValue();
                        // $region         = $objPHPExcel->getActiveSheet()->getCell(''.$i)->getCalculatedValue(); //
                        $region         = null; //
                        // $canal          = $objPHPExcel->getActiveSheet()->getCell(''.$i)->getCalculatedValue(); //
                        $canal          = null; //
                        $tipoAtencion   = $objPHPExcel->getActiveSheet()->getCell('S'.$i)->getCalculatedValue();
                        $canalAtencion  = $objPHPExcel->getActiveSheet()->getCell('P'.$i)->getCalculatedValue();
                        $segmentoClienteFinal  = $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getCalculatedValue();
                        $subSegmento      = $objPHPExcel->getActiveSheet()->getCell('R'.$i)->getCalculatedValue();
                        $segmentoRegional = $objPHPExcel->getActiveSheet()->getCell('T'.$i)->getCalculatedValue();
                        // $gerenteRegional  = $objPHPExcel->getActiveSheet()->getCell(''.$i)->getCalculatedValue(); //
                        $gerenteRegional  = null; //
                        // $gerenteZona      = $objPHPExcel->getActiveSheet()->getCell(''.$i)->getCalculatedValue(); //
                        $gerenteZona      = null; //
                        $ejecutivo        = $objPHPExcel->getActiveSheet()->getCell('M'.$i)->getCalculatedValue(); // 
                        $identificador    = $objPHPExcel->getActiveSheet()->getCell('U'.$i)->getCalculatedValue();

                        $ex_estado    = $objPHPExcel->getActiveSheet()->getCell('V'.$i)->getCalculatedValue();

                        // NUEVOS
                        $ex_codorganizacionventas   = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                        $ex_organizacionventas      = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                        $ex_codigodivision          = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                        $ex_division                = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                        $ex_ruc                     = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                        $ex_disponibleuno           = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getCalculatedValue();
                        $ex_codigopais              = $objPHPExcel->getActiveSheet()->getCell('W'.$i)->getCalculatedValue();
                        $ex_pais                    = $objPHPExcel->getActiveSheet()->getCell('X'.$i)->getCalculatedValue();
                        $ex_codigodepartamento      = $objPHPExcel->getActiveSheet()->getCell('Y'.$i)->getCalculatedValue();
                        $ex_departamento            = $objPHPExcel->getActiveSheet()->getCell('Z'.$i)->getCalculatedValue();
                        $ex_codigoprovincia         = $objPHPExcel->getActiveSheet()->getCell('AA'.$i)->getCalculatedValue();
                        $ex_provincia               = $objPHPExcel->getActiveSheet()->getCell('AB'.$i)->getCalculatedValue();
                        $ex_codigodistrito          = $objPHPExcel->getActiveSheet()->getCell('AC'.$i)->getCalculatedValue();
                        $ex_distrito                = $objPHPExcel->getActiveSheet()->getCell('AD'.$i)->getCalculatedValue();
                        $ex_direccion               = $objPHPExcel->getActiveSheet()->getCell('AE'.$i)->getCalculatedValue();
                        $ex_zonaventa               = $objPHPExcel->getActiveSheet()->getCell('AF'.$i)->getCalculatedValue();


                        $essac = false;

                        if($identificador == "MANUAL" || $identificador == "NO COMPARTE"){
                            $essac = true;
                        }

                        if(isset($soldTo)){
                            $clinombreSuc        = $soldTo;
                            $clicodigoSuc        = $codSoldTo;
                            $clicodigoshiptoSuc  = $codShipTo;
                            $clishiptoSuc        = $shipTo;
                            $clihmlSuc           = $clienteHml;
                            $clisuchmlSuc        = $sucHml;
                            $clidepartamentoSuc  = $departamento;
                            $cligrupohmlSuc      = $grupoHml;
                            $clitvSuc            = $tv;
                            $clizonaSuc          = $zona;
                            $cliregionSuc        = $region;
                            $clicanalSuc         = $canal;
                            $clitipoatencionSuc  = $tipoAtencion;
                            $clicanalatencionSuc = $canalAtencion;
                            $clisegmentoclientefinalSuc = $segmentoClienteFinal;
                            $clisubsegmentoSuc      = $subSegmento;
                            $clisegmentoregionalSuc = $segmentoRegional;
                            $cligerenteregionalSuc  = $gerenteRegional;
                            $cligerentezonaSuc      = $gerenteZona;
                            $cliejecutivoSuc        = $ejecutivo;
                            $cliidentificadoraplicativoSuc = $identificador;

                            $cli = cliclientes::where('clicodigoshipto', $clicodigoshiptoSuc)->first();

                            if($cli){
                                $camposEditados = [];

                                if($cli->clinombre != $clinombreSuc){
                                    $camposEditados[] = "NOMBRE ANTES: ".$cli->clinombre." AHORA: ".$clinombreSuc;
                                    $cli->clinombre = $clinombreSuc;
                                }

                                if($cli->clicodigo != $clicodigoSuc){
                                    $camposEditados[] = "CODIGO ANTES: ".$cli->clicodigo." AHORA: ".$clicodigoSuc;
                                    $cli->clicodigo = $clicodigoSuc;
                                }
                                
                                if($cli->clicodigoshipto != $clicodigoshiptoSuc){
                                    $camposEditados[] = "CODIGO-SHIPTO ANTES: ".$cli->clicodigoshipto." AHORA: ".$clicodigoshiptoSuc;
                                    $cli->clicodigoshipto  = $clicodigoshiptoSuc;
                                }

                                if($cli->clishipto != $clishiptoSuc){
                                    $camposEditados[] = "SHIPTO ANTES: ".$cli->clishipto." AHORA: ".$clishiptoSuc;
                                    $cli->clishipto = $clishiptoSuc;
                                }
                                
                                if($cli->clihml != $clihmlSuc){
                                    $camposEditados[] = "HML ANTES: ".$cli->clihml." AHORA: ".$clihmlSuc;
                                    $cli->clihml = $clihmlSuc;
                                }
                                
                                if($cli->clisuchml != $clisuchmlSuc){
                                    $camposEditados[] = "SUCHML ANTES: ".$cli->clisuchml." AHORA: ".$clisuchmlSuc;
                                    $cli->clisuchml = $clisuchmlSuc;
                                }
                                
                                if($cli->clidepartamento != $clidepartamentoSuc){
                                    $camposEditados[] = "DEPARTAMENTO ANTES: ".$cli->clidepartamento." AHORA: ".$clidepartamentoSuc;
                                    $cli->clidepartamento = $clidepartamentoSuc;
                                }
                                
                                if($cli->cligrupohml != $cligrupohmlSuc){
                                    $camposEditados[] = "GRUPO-HML ANTES: ".$cli->cligrupohml." AHORA: ".$cligrupohmlSuc;
                                    $cli->cligrupohml = $cligrupohmlSuc;
                                }

                                if($cli->clitv != $clitvSuc){
                                    $camposEditados[] = "TV ANTES: ".$cli->clitv." AHORA: ".$clitvSuc;
                                    $cli->clitv = $clitvSuc;
                                }
                                
                                if($cli->clizona != $clizonaSuc){
                                    $camposEditados[] = "ZONA ANTES: ".$cli->clizona." AHORA: ".$clizonaSuc;
                                    $cli->clizona = $clizonaSuc;
                                }
                                
                                if($cli->cliregion != $cliregionSuc){
                                    $camposEditados[] = "REGION ANTES: ".$cli->cliregion." AHORA: ".$cliregionSuc;
                                    $cli->cliregion = $cliregionSuc;
                                }
                                
                                if($cli->clicanal != $clicanalSuc){
                                    $camposEditados[] = "CANAL ANTES: ".$cli->clicanal." AHORA: ".$clicanalSuc;
                                    $cli->clicanal = $clicanalSuc;
                                }
                                
                                if($cli->clitipoatencion != $clitipoatencionSuc){
                                    $camposEditados[] = "TIPO-ATENCION ANTES: ".$cli->clitipoatencion." AHORA: ".$clitipoatencionSuc;
                                    $cli->clitipoatencion = $clitipoatencionSuc;
                                }
                                
                                if($cli->clicanalatencion != $clicanalatencionSuc){
                                    $camposEditados[] = "CANAL-ATENCION ANTES: ".$cli->clicanalatencion." AHORA: ".$clicanalatencionSuc;
                                    $cli->clicanalatencion = $clicanalatencionSuc;
                                }
                                
                                if($cli->clisegmentoclientefinal != $clisegmentoclientefinalSuc){
                                    $camposEditados[] = "SEGMENTO-CLIENTE-FINAL ANTES: ".$cli->clisegmentoclientefinal." AHORA: ".$clisegmentoclientefinalSuc;
                                    $cli->clisegmentoclientefinal = $clisegmentoclientefinalSuc;
                                }
                                
                                if($cli->clisubsegmento != $clisubsegmentoSuc){
                                    $camposEditados[] = "SUB-SEGMENTO ANTES: ".$cli->clisubsegmento." AHORA: ".$clisubsegmentoSuc;
                                    $cli->clisubsegmento = $clisubsegmentoSuc;
                                }
                                
                                if($cli->clisegmentoregional != $clisegmentoregionalSuc){
                                    $camposEditados[] = "SEGMENTO-ORIGINAL ANTES: ".$cli->clisegmentoregional." AHORA: ".$clisegmentoregionalSuc;
                                    $cli->clisegmentoregional = $clisegmentoregionalSuc;
                                }
                                
                                if($cli->cligerenteregional != $cligerenteregionalSuc){
                                    $camposEditados[] = "GERENTE-REGIONAL ANTES: ".$cli->cligerenteregional." AHORA: ".$cligerenteregionalSuc;
                                    $cli->cligerenteregional = $cligerenteregionalSuc;
                                }
                                
                                if($cli->cligerentezona != $cligerentezonaSuc){
                                    $camposEditados[] = "GERENTE-ZONA ANTES: ".$cli->cligerentezona." AHORA: ".$cligerentezonaSuc;
                                    $cli->cligerentezona = $cligerentezonaSuc;
                                }
                                
                                if($cli->cliejecutivo != $cliejecutivoSuc){
                                    $camposEditados[] = "EJECUTIVO ANTES: ".$cli->cliejecutivo." AHORA: ".$cliejecutivoSuc;
                                    $cli->cliejecutivo = $cliejecutivoSuc;
                                }
                                
                                if($cli->cliidentificadoraplicativo != $cliidentificadoraplicativoSuc){
                                    $camposEditados[] = "IDENTIFICADOR-APLICATIVO  ANTES: ".$cli->cliidentificadoraplicativo." AHORA: ".$cliidentificadoraplicativoSuc;
                                    $cli->cliidentificadoraplicativo = $cliidentificadoraplicativoSuc;
                                }

                                if($cli->cliclientesac != $essac){
                                    $camposEditados[] = "ES SAC  ANTES: ".$cli->cliclientesac." AHORA: ".$essac;
                                    $cli->cliclientesac = $essac;
                                }

                                $mystring  = $ex_estado;
                                $findme    = 'NAC';
                                $bloqueado = false;

                                $pos = strpos($mystring, $findme);

                                if ($pos === false) {
                                    $bloqueado = false;
                                }else {
                                    $bloqueado = true;
                                }

                                if($cli->clibloqueado != $bloqueado){
                                    $camposEditados[] = "ESTADO ANTES: ".$cli->clibloqueado." AHORA: ".$bloqueado;
                                }
                                $cli->clibloqueado = $bloqueado;

                                if(sizeof($camposEditados) > 0){
                                    $cli->update();

                                    $logs["SUCURSAL_EDITADO"][] = array(
                                        "sucid" => $cli->cliid,
                                        "soldto" => $cli->clicodigo,
                                        "camposEditados" => $camposEditados
                                    );

                                }else{
                                    $logs["SUCURSAL_NO_EDITADO"][] = array(
                                        "sucid" => $cli->cliid,
                                        "soldto" => $cli->clicodigo,
                                        "camposEditados" => $camposEditados
                                    );
                                }

                            }else{
                                $clin = new cliclientes;
                                $clin->clinombre        = $clinombreSuc;
                                $clin->clicodigo        = $clicodigoSuc;
                                $clin->clicodigoshipto  = $clicodigoshiptoSuc;
                                $clin->clishipto        = $clishiptoSuc;
                                $clin->clihml           = $clihmlSuc;
                                $clin->clisuchml        = $clisuchmlSuc;
                                $clin->clidepartamento  = $clidepartamentoSuc;
                                $clin->cligrupohml      = $cligrupohmlSuc;
                                $clin->clitv            = $clitvSuc;
                                $clin->clizona          = $clizonaSuc;
                                $clin->cliregion        = $cliregionSuc;
                                $clin->clicanal         = $clicanalSuc;
                                $clin->clitipoatencion  = $clitipoatencionSuc;
                                $clin->clicanalatencion = $clicanalatencionSuc;
                                $clin->clisegmentoclientefinal = $clisegmentoclientefinalSuc;
                                $clin->clisubsegmento       = $clisubsegmentoSuc;
                                $clin->clisegmentoregional  = $clisegmentoregionalSuc;
                                $clin->cligerenteregional   = $cligerenteregionalSuc;
                                $clin->cligerentezona       = $cligerentezonaSuc;
                                $clin->cliejecutivo         = $cliejecutivoSuc;
                                $clin->cliidentificadoraplicativo = $cliidentificadoraplicativoSuc;
                                $clin->cliclientesac = $essac;

                                if( $ex_estado == "" ){
                                    
                                }else{
                                    $clin->clibloqueado = $essac;
                                }


                                $mystring = $ex_estado;
                                $findme   = 'ACTIVO';

                                $pos = strpos($mystring, $findme);

                                if ($pos === false) {
                                    
                                    $clin->clibloqueado = true;
                                } else {
                                    
                                    $clin->clibloqueado = false;
                                }
                                


                                if($clin->save()){
                                    $logs["NUEVOS_SUCURSAL"][] = array(
                                        "sucid" => $clin->cliid,
                                        "soldto" => $clin->clicodigo,
                                    );
                                }
                            }
                        }

                    }


                }
            
                if($usu->usuid != 1){

                    $care = carcargasarchivos::find($carid);
                    $care->carexito = 1;
                    $care->update();

                    $fec = fecfechas::where('fecmesabierto', true)->first(['fecid']);
                    $fecid = $fec->fecid;

                    $espe = espestadospendientes::where('fecid', $fecid)
                                                ->where('espbasedato', "Master Clientes")
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
                                    $aree->areporcentaje = $porcentaje;
                                }

                                $aree->update();
                            } 
                        }

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
            'CARGAR DATA DE CLIENTES AL SISTEMA ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/clientes', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;
    }
}
