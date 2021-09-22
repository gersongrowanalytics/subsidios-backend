<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\usuusuarios;
use App\Models\fecfechas;
use App\Models\carcargasarchivos;

class MetCargarMaestraFechasController extends Controller
{
    public function CargarMaestraFechas(Request $request)
    {
        date_default_timezone_set("America/Lima");
        $fechaActual = date('Y-m-d');

        $logs = array(
            "MENSAJE" => "",
            "NUMERO_LINEAS_EXCEL" => "",
            "NUEVA_FECHA" => [],
            "FECHA_EDITADO" => array(),
            "FECHA_NO_EDITADO" => array()
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

            $fichero_subido = base_path().'/public/Sistema/Modulos/CargaArchivos/Fechas/'.basename($codigoArchivoAleatorio.'-'.$usu->usuid.'-'.$usu->usuusuario.'-'.$fechaActual.'-'.$_FILES['file']['name']);

            $ex_file_name = explode(".", $_FILES['file']['name']);
            $carn = new carcargasarchivos;
            $carn->tcaid        = 11;
            $carn->usuid        = $usu->usuid;
            $carn->carnombre    = $_FILES['file']['name'];
            $carn->carextension = $ex_file_name[1];
            $carn->carurl       = env('APP_URL').$ubicacionArchivo;
            $carn->carexito     = 0;
            $carn->save();
            $carid = $carn->carid;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {

                $objPHPExcel    = IOFactory::load($fichero_subido);
                $objPHPExcel->setActiveSheetIndex(0);
                $numRows        = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $ultimaColumna  = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();

                $logs['NUMERO_LINEAS_EXCEL'] = $numRows;

                for ($i=2; $i <= $numRows ; $i++) {
                    $fecha        = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getCalculatedValue();
                    $diaNumero    = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getCalculatedValue();
                    $dia          = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getCalculatedValue();
                    $mesNumero    = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getCalculatedValue();
                    $mes          = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getCalculatedValue();
                    $anio         = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getCalculatedValue();
                    $semanaMes    = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getCalculatedValue();
                    $semanaAnio   = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getCalculatedValue();
                    $trimestre    = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getCalculatedValue();
                    $cuatrimestre = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getCalculatedValue();

                    $diaMesTexto = $this->CambiarTexto($dia, $mes);

                    $dia = $diaMesTexto['diaTexto'];
                    $mes = $diaMesTexto['mesTexto'];

                    $mesAbreviado = "";
                    if($mes == "Enero"){
                        $mesAbreviado = "ENE";
                    }else if($mes == "Febrero"){
                        $mesAbreviado = "FEB";
                    }else if($mes == "Marzo"){
                        $mesAbreviado = "MAR";
                    }else if($mes == "Abril"){
                        $mesAbreviado = "ABR";
                    }else if($mes == "Mayo"){
                        $mesAbreviado = "MAY";
                    }else if($mes == "Junio"){
                        $mesAbreviado = "JUN";
                    }else if($mes == "Julio"){
                        $mesAbreviado = "JUL";
                    }else if($mes == "Agosto"){
                        $mesAbreviado = "AGO";
                    }else if($mes == "Setiembre"){
                        $mesAbreviado = "SET";
                    }else if($mes == "Octubre"){
                        $mesAbreviado = "OCT";
                    }else if($mes == "Noviembre"){
                        $mesAbreviado = "NOV";
                    }else if($mes == "Diciembre"){
                        $mesAbreviado = "DIC";
                    }

                    $fecmesabreviacionFecha = $mesAbreviado;
                    $fecdianumeroFecha = $diaNumero;
                    $fecmesnumeroFecha = $mesNumero;
                    $fecanionumeroFecha = $anio;
                    $fecdiatextoFecha = $dia;
                    $fecmestextoFecha = $mes;
                    $fecaniotextoFecha = "";
                    
                    $arrayFecha = explode("/", $fecha);
                    $fecfechaFecha = $arrayFecha[2]."/".$arrayFecha[1]."/".$arrayFecha[0];

                    $fec = fecfechas::where('fecfecha', $fecfechaFecha)->first();

                    if($fec){
                        
                        $camposEditados = [];

                        if($fec->fecmesabreviacion != $fecmesabreviacionFecha){
                            $camposEditados[] = "ABREVIACION ANTES: ".$fec->fecmesabreviacion." AHORA: ".$fecmesabreviacionFecha;
                            $fec->fecmesabreviacion = $fecmesabreviacionFecha;
                        }

                        if($fec->fecdianumero != $fecdianumeroFecha){
                            $camposEditados[] = "DIA-NUMERO ANTES: ".$fec->fecdianumero." AHORA: ".$fecdianumeroFecha;
                            $fec->fecdianumero = $fecdianumeroFecha;
                        }
                        
                        if($fec->fecmesnumero != $fecmesnumeroFecha){
                            $camposEditados[] = "MES-NUMERO ANTES: ".$fec->fecmesnumero." AHORA: ".$fecmesnumeroFecha;
                            $fec->fecmesnumero = $fecmesnumeroFecha;
                        }
                        
                        if($fec->fecanionumero != $fecanionumeroFecha){
                            $camposEditados[] = "ANIO-NUMERO ANTES: ".$fec->fecanionumero." AHORA: ".$fecanionumeroFecha;
                            $fec->fecanionumero = $fecanionumeroFecha;
                        }
                        
                        if($fec->fecdiatexto != $fecdiatextoFecha){
                            $camposEditados[] = "DIA-TEXTO ANTES: ".$fec->fecdiatexto." AHORA: ".$fecdiatextoFecha;
                            $fec->fecdiatexto = $fecdiatextoFecha;
                        }
                        
                        if($fec->fecmestexto != $fecmestextoFecha){
                            $camposEditados[] = "MES-TEXTO ANTES: ".$fec->fecmestexto." AHORA: ".$fecmestextoFecha;
                            $fec->fecmestexto = $fecmestextoFecha;
                        }
                        
                        if($fec->fecaniotexto != $fecaniotextoFecha){
                            $camposEditados[] = "ANIO-TEXTO ANTES: ".$fec->fecmestexto." AHORA: ".$fecmestextoFecha;
                            $fec->fecaniotexto = $fecaniotextoFecha;
                        }

                        if(sizeof($camposEditados) > 0){

                            if($fec->update()){

                                $logs["FECHA_EDITADO"][] = array(
                                    "fecid" => $fec->fecid,
                                    "fecha" => $fec->fecfecha,
                                    "camposEditados" => $camposEditados
                                );
    
                            }else{
                                $respuesta = false;
                                $mensaje = "Lo sentimos, la fecha no se pudo editar en el sistema. LINEA : ".$i;
                            }

                        }else{
                            $logs["FECHA_NO_EDITADO"][] = array(
                                "fecid" => $fec->fecid,
                                    "fecha" => $fec->fecfecha,
                                    "camposEditados" => $camposEditados
                            );
                        }

                    }else{
                        $fecn = new fecfechas;
                        $fecn->fecfecha = $fecfechaFecha;
                        $fecn->fecmesabreviacion = $fecmesabreviacionFecha;
                        $fecn->fecdianumero = $fecdianumeroFecha;
                        $fecn->fecmesnumero = $fecmesnumeroFecha;
                        $fecn->fecanionumero = $fecanionumeroFecha;
                        $fecn->fecdiatexto = $fecdiatextoFecha;
                        $fecn->fecmestexto = $fecmestextoFecha;
                        $fecn->fecaniotexto = $fecaniotextoFecha;
                        if($fecn->save()){
                            $logs['NUEVA_FECHA'][] = "FECID: ".$fecn->fecid." | FECHA: ".$fecn->fecfecha;
                        }else{
                            $respuesta = false;
                            $mensaje = "Lo sentimos, la fecha no se pudo guardar en el sistema. LINEA : ".$i; 
                        }
                    }

                }

                $care = carcargasarchivos::find($carid);
                $care->carexito = 1;
                $care->update();

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
            'CARGAR DATA DE FECHAS AL SISTEMA ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/fechas', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;
    }

    public function CambiarTexto($diaTexto, $mesTexto)
    {

        if($diaTexto == "Monday"){
            $diaTexto = "Lunes";
        }else if($diaTexto == "Tuesday"){
            $diaTexto = "Martes";
        }else if($diaTexto == "Wednesday"){
            $diaTexto = "Miercoles";
        }else if($diaTexto == "Thursday"){
            $diaTexto = "Jueves";
        }else if($diaTexto == "Friday"){
            $diaTexto = "Viernes";
        }else if($diaTexto == "Saturday"){
            $diaTexto = "Sabado";
        }else if($diaTexto == "Sunday"){
            $diaTexto = "Domingo";
        }

        if($mesTexto == "January"){
            $mesTexto = "Enero";
        }else if($mesTexto == "February"){
            $mesTexto = "Febrero";
        }else if($mesTexto == "March"){
            $mesTexto = "Marzo";
        }else if($mesTexto == "April"){
            $mesTexto = "Abril";
        }else if($mesTexto == "May"){
            $mesTexto = "Mayo";
        }else if($mesTexto == "June"){
            $mesTexto = "Junio";
        }else if($mesTexto == "July"){
            $mesTexto = "Julio";
        }else if($mesTexto == "August"){
            $mesTexto = "Agosto";
        }else if($mesTexto == "September"){
            $mesTexto = "Setiembre";
        }else if($mesTexto == "October"){
            $mesTexto = "Octubre";
        }else if($mesTexto == "November"){
            $mesTexto = "Noviembre";
        }else if($mesTexto == "December"){
            $mesTexto = "Diciembre";
        }

        return array(
            "diaTexto" => $diaTexto,
            "mesTexto" => $mesTexto
        );

    }
}
