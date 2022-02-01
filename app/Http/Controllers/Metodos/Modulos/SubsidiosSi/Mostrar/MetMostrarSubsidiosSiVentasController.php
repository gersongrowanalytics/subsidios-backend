<?php

namespace App\Http\Controllers\Metodos\Modulos\SubsidiosSi\Mostrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fecfechas;
use ZipArchive;

class MetMostrarSubsidiosSiVentasController extends Controller
{
    public function MetMostrarSubsidiosSiVentas(Request $request)
    {

        $re_fechainicio = $request['fechaInicio'];
        $re_fechafinal  = $request['fechaFinal'];

        if($re_fechainicio != null){
            $re_fechainicio = date("Y-m-d", strtotime($re_fechainicio));
            $re_fechafinal  = date("Y-m-d", strtotime($re_fechafinal));
        }

        $links = [];

        $fecs = fecfechas::whereBetween('fecfecha', [$re_fechainicio, $re_fechafinal])
                        ->distinct('fecid')
                        ->get([
                            'fecid',
                            'fecanionumero',
                            'fecmestexto'
                        ]);

        $arr_fechas = array();

        if(sizeof($fecs) > 0){

            foreach ($fecs as $key => $fec) {
                
                $encontroFecha = false;

                foreach ($arr_fechas as $key => $arr_fecha) {
                    if($arr_fecha['anio'] == $fec->fecanionumero && $arr_fecha['mes'] == $fec->fecmestexto ){
                        $encontroFecha = true;
                    }
                }

                if($encontroFecha == false){
                    if($fec->fecanionumero == "2021" && $fec->fecmestexto == "Diciembre"){
                        $links[] = "SubsidiosVentas/"."2021"."/Subsidios"."Noviembre".".xlsx";
                    }else if($fec->fecanionumero == "2022" && $fec->fecmestexto == "Enero"){
                        $links[] = "SubsidiosVentas/"."2021"."/Subsidios"."Noviembre".".xlsx";
                    }else{
                        $links[] = "SubsidiosVentas/".$fec->fecanionumero."/Subsidios".$fec->fecmestexto.".xlsx";
                    }


                    $arr_fechas[] = array(
                        "anio" => $fec->fecanionumero,
                        "mes"  => $fec->fecmestexto,
                    );
                }

            }

        }else{

        }


        if(sizeof($links) > 1){

            if( file_exists("SubsidiosVentas/Consolidados/".$re_fechainicio."-hasta-".$re_fechafinal."-comprimido.zip")  ){ //Destruye el archivo temporal
                unlink("SubsidiosVentas/Consolidados/".$re_fechainicio."-hasta-".$re_fechafinal."-comprimido.zip");
            }

            $fileName="comprimido.rar";
            // Creamos un instancia de la clase ZipArchive
            $zip = new ZipArchive();
            // Creamos y abrimos un archivo zip temporal
            $zip->open("SubsidiosVentas/Consolidados/".$re_fechainicio."-hasta-".$re_fechafinal."-comprimido.zip", ZipArchive::CREATE);
            // Añadimos un directorio
            // $dir = 'miDirectorio';
            // $zip->addEmptyDir($dir);

            foreach ($links as $key => $link) {
                
                // $zip->addFile("SubsidiosVentas/Consolidados".$nombreArchivo);
                $zip->addFile($link);

                if($key+1 == sizeof($links)){
                    $zip->close();
                    // Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
                    // header("Content-type: application/octet-stream");
                    // header('Content-disposition: attachment; filename="'. urlencode($fileName).'"');
                    // leemos el archivo creado
                    // readfile('miarchivo.zip');
                    // Por último eliminamos el archivo temporal creado
                    // unlink('miarchivo.zip');//Destruye el archivo temporal
                }

            }

            $ubicacion = "SubsidiosVentas/Consolidados/".$re_fechainicio."-hasta-".$re_fechafinal."-comprimido.zip";
            $links = [$ubicacion];
        }

        $requestsalida = response()->json([
            "links" => $links,
            "fecs" => $fecs,
        ]);

        return $requestsalida;

    }
}
