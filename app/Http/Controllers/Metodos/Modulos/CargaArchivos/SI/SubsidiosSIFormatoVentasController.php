<?php

namespace App\Http\Controllers\Metodos\Modulos\CargaArchivos\SI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AuditoriaController;
use App\Models\carcargasarchivos;
use App\Models\usuusuarios;

class SubsidiosSIFormatoVentasController extends Controller
{
    public function MetSubsidiosSIFormatoVentas(Request $request)
    {

        $logs = array();
        $pkis = array();
        $respuesta = true;
        $mensaje = "El archivo se actualizo correctamente";

        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $archivo  = $_FILES['file']['name'];

        $usu = usuusuarios::where('usutoken', $usutoken)->first(['usuid', 'usuusuario', 'perid']);

        $codigoArchivoAleatorio = mt_rand(0, mt_getrandmax())/mt_getrandmax();
        $nombreArchivo = $codigoArchivoAleatorio.'-'.$_FILES['file']['name'];

        $ubicacionArchivo = '/SubsidiosVentas/Consolidados/'.basename($codigoArchivoAleatorio.'-'.$_FILES['file']['name']);
        $fichero_subido = base_path().'/public'.$ubicacionArchivo;

        $ex_file_name = explode(".", $_FILES['file']['name']);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $fichero_subido)) {

            $care = carcargasarchivos::find(492);
            $care->carnombre = $nombreArchivo;
            $care->carurl = env('APP_URL').$ubicacionArchivo;;
            $care->update();

        }else{
            $respuesta = false;
            $mensaje = "Lo sentimos, el archivo no se pudo guardar en el sistema";
        }

        $logs["TITULO"] = $mensaje;
        $logs["MENSAJE"] = $mensaje;
        $logs["RESPUESTA"] = $respuesta;

        $requestsalida = response()->json([
            "respuesta" => $respuesta,
            "mensaje"   => $mensaje,
            "logs" => $logs,
        ]);

        $AuditoriaController = new AuditoriaController;
        $registrarAuditoria  = $AuditoriaController->registrarAuditoria(
            $usutoken, // token
            $usu->usuid, // usuid
            null, // audip
            $fichero_subido, // audjsonentrada
            $requestsalida,// audjsonsalida
            'CARGAR DATA DE SUBSIDIOS SI FORMATO DE VENTAS ', //auddescripcion
            'IMPORTAR', // audaccion
            '/modulo/cargaArchivos/subsidios-si-formato-ventas', //audruta
            $pkis, // audpk
            $logs // log
        );

        return $requestsalida;

    }

    public function ObtenerLinkSubsidiosSIVentas()
    {

        $car = carcargasarchivos::find(492);

        return response()->json([
            'link' => $car->carurl
        ]);
    }
}
