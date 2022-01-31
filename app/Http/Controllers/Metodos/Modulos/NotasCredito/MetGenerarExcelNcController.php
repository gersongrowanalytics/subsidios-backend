<?php

namespace App\Http\Controllers\Metodos\Modulos\NotasCredito;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fecfechas;
use App\Models\usuusuarios;
use App\Models\sdesubsidiosdetalles;
use App\Models\sfssubsidiosfacturassi;
use DB;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class MetGenerarExcelNcController extends Controller
{
    public function MetGenerarExcelNc(Request $request)
    {

        $re_anio = $request['anio'];
        $re_mes = $request['mes'];

        $fec = fecfechas::where('fecanionumero', $re_anio)
                        ->where('fecmestexto', $re_mes)
                        ->first();

        // $re_fechasolicitada = $request['fecha'];
        // $fecid = $re_fechasolicitada;

        // $fec = fecfechas::find($fecid);

        $fecid = $fec->fecid;

        $re_filtrozonas        = $request['filtrozonas'];
        $re_zonas              = $request['zonas'];

        $re_filtroterritorio   = $request['filtroterritorio'];
        $re_territorios        = $request['territorios'];

        $re_filtrodistribuidor = $request['filtrodistribuidor'];
        $re_distribuidores     = $request['distribuidores'];

        date_default_timezone_set("America/Lima");
        $fechaActual = date('d-m-Y');
        
        $usutoken = $request->header('api_token');
        if(!isset($usutoken)){
            $usutoken = "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72";
        }

        $usu = usuusuarios::join('perpersonas as per', 'per.perid', 'usuusuarios.perid')
                            ->where('usutoken', $usutoken)
                            ->first([
                                'usuid',
                                'pernombrecompleto'
                            ]);


        $array_generar_excel = array();

        if($usu){


            if($re_filtrozonas == true){

                $zonas = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                            ->where('fecid', $fecid)
                                            ->where('sdeaprobado', true)
                                            ->distinct('cli.clizona')
                                            ->get([
                                                'cli.clizona'
                                            ]);

                // foreach ($zonas as $key => $zona) {
                foreach ($re_zonas as $key => $zona) {

                    $array_excel = array(
                        "excelnombre" => $zona['clizona'],
                        "cantidaddt"  => 0,
                        "solicitado"  => $usu->pernombrecompleto,
                        "fecha"       => $fechaActual,
                        "mes"         => $fec->fecmesabreviacion,
                        "clientes"    => array()
                    );


                    $destinatarios = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                                        ->where('fecid', $fecid)
                                                        ->where('sdeaprobado', true)
                                                        ->where('clizona', $zona['clizona'])
                                                        ->distinct('cli.clicodigoshipto')
                                                        ->get([
                                                            'cli.clicodigoshipto',
                                                            'clisuchml',
                                                            'clicodigo',
                                                            'clihml',
                                                        ]);

                    foreach ($destinatarios as $key => $destinatario) {

                        $sfss = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                            ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                            ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                            ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                            ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                            ->where('sdecodigodestinatario', $destinatario->clicodigoshipto)
                                            ->groupBy('fsifactura')
                                            ->groupBy('fdsmaterial')
                                            ->groupBy('pronombre')
                                            ->select(
                                                'fsi.fsifactura',
                                                'fdsmaterial',
                                                'pronombre',
                                                DB::raw("SUM(sfsvalorizado) as sfsvalorizado"),
                                            )
                                            ->get([
                                                'fsi.fsifactura',
                                                'fdsmaterial',
                                                'pronombre',
                                                'sfsvalorizado'
                                            ]);

                        $sumaSfs = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                                        ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                                        ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                                        ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                                        ->where('sdecodigodestinatario', $destinatario->clicodigoshipto)
                                                        ->sum('sfsvalorizado');

                        $array_excel["clientes"][] = array(
                            "destinatario" => $destinatario->clicodigoshipto,
                            "cliente"      => $destinatario->clisuchml,
                            "solicitante"  => $destinatario->clicodigo,
                            "clihml"       => $destinatario->clihml,
                            "total"        => $sumaSfs,
                            "facturas"     => $sfss
                        );

                    }

                    $array_generar_excel[] = $array_excel;


                }
                
                

            }else if($re_filtroterritorio == true){





                foreach ($re_territorios as $key => $territorio) {

                    $array_excel = array(
                        "excelnombre" => $territorio['data'],
                        "cantidaddt"  => 0,
                        "solicitado"  => $usu->pernombrecompleto,
                        "fecha"       => $fechaActual,
                        "mes"         => $fec->fecmesabreviacion,
                        "clientes"    => array()
                    );


                    $destinatarios = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                                        ->where('fecid', $fecid)
                                                        ->where('sdeaprobado', true)
                                                        ->where('clitv', $territorio['data'])
                                                        ->distinct('cli.clicodigoshipto')
                                                        ->get([
                                                            'cli.clicodigoshipto',
                                                            'clisuchml',
                                                            'clicodigo',
                                                            'clihml',
                                                        ]);

                    foreach ($destinatarios as $key => $destinatario) {

                        $sfss = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                            ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                            ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                            ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                            ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                            ->where('sdecodigodestinatario', $destinatario->clicodigoshipto)
                                            ->groupBy('fsifactura')
                                            ->groupBy('fdsmaterial')
                                            ->groupBy('pronombre')
                                            ->select(
                                                'fsi.fsifactura',
                                                'fdsmaterial',
                                                'pronombre',
                                                DB::raw("SUM(sfsvalorizado) as sfsvalorizado"),
                                            )
                                            ->get([
                                                'fsi.fsifactura',
                                                'fdsmaterial',
                                                'pronombre',
                                                'sfsvalorizado'
                                            ]);

                        $sumaSfs = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                                        ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                                        ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                                        ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                                        ->where('sdecodigodestinatario', $destinatario->clicodigoshipto)
                                                        ->sum('sfsvalorizado');

                        $array_excel["clientes"][] = array(
                            "destinatario" => $destinatario->clicodigoshipto,
                            "cliente"      => $destinatario->clisuchml,
                            "solicitante"  => $destinatario->clicodigo,
                            "clihml"       => $destinatario->clihml,
                            "total"        => $sumaSfs,
                            "facturas"     => $sfss
                        );

                    }

                    $array_generar_excel[] = $array_excel;


                }





            }else if($re_filtrodistribuidor == true){

                foreach ($re_distribuidores as $key => $distribuidor) {

                    $array_excel = array(
                        "excelnombre" => $distribuidor['clisuchml'],
                        "cantidaddt"  => 0,
                        "solicitado"  => $usu->pernombrecompleto,
                        "fecha"       => $fechaActual,
                        "mes"         => $fec->fecmesabreviacion,
                        "clientes"    => array()
                    );


                    $destinatarios = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                                        ->where('fecid', $fecid)
                                                        ->where('sdeaprobado', true)
                                                        ->where('clisuchml', $distribuidor['clisuchml'])
                                                        ->distinct('cli.clicodigoshipto')
                                                        ->get([
                                                            'cli.clicodigoshipto',
                                                            'clisuchml',
                                                            'clicodigo',
                                                            'clihml',
                                                        ]);

                    foreach ($destinatarios as $key => $destinatario) {

                        $sfss = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                            ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                            ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                            ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                            ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                            ->where('sdecodigodestinatario', $destinatario->clicodigoshipto)
                                            ->groupBy('fsifactura')
                                            ->groupBy('fdsmaterial')
                                            ->groupBy('pronombre')
                                            ->select(
                                                'fsi.fsifactura',
                                                'fdsmaterial',
                                                'pronombre',
                                                DB::raw("SUM(sfsvalorizado) as sfsvalorizado"),
                                            )
                                            ->get([
                                                'fsi.fsifactura',
                                                'fdsmaterial',
                                                'pronombre',
                                                'sfsvalorizado'
                                            ]);

                        $sumaSfs = sfssubsidiosfacturassi::join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                                        ->join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                                        ->join('proproductos as pro', 'pro.proid', 'fds.proid')
                                                        ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                                        ->where('sdecodigodestinatario', $destinatario->clicodigoshipto)
                                                        ->sum('sfsvalorizado');

                        $array_excel["clientes"][] = array(
                            "destinatario" => $destinatario->clicodigoshipto,
                            "cliente"      => $destinatario->clisuchml,
                            "solicitante"  => $destinatario->clicodigo,
                            "clihml"       => $destinatario->clihml,
                            "total"        => $sumaSfs,
                            "facturas"     => $sfss
                        );

                    }

                    $array_generar_excel[] = $array_excel;


                }

            }else{

            }


        }else{

        }

        $cantidadExcelsGenerar = sizeof($array_generar_excel);
        $nombreArchivos = [];

        foreach ($array_generar_excel as $key => $excel) {
            $nombreArchivos[] = $this->ArmarExcelNc($excel, $re_anio, $re_mes);   
        }

        $ubicacion = "/";

        if($cantidadExcelsGenerar > 1){
            
            if( file_exists("Subsidios/".$re_anio."-".$re_mes."-comprimido.zip")  ){ //Destruye el archivo temporal
                unlink("Subsidios/".$re_anio."-".$re_mes."-comprimido.zip");
            }

            $fileName="comprimido.rar";
            // Creamos un instancia de la clase ZipArchive
            $zip = new ZipArchive();
            // Creamos y abrimos un archivo zip temporal
            $zip->open("Subsidios/".$re_anio."-".$re_mes."-comprimido.zip",ZipArchive::CREATE);
            // Añadimos un directorio
            // $dir = 'miDirectorio';
            // $zip->addEmptyDir($dir);

            foreach ($nombreArchivos as $key => $nombreArchivo) {
                
                $zip->addFile("Subsidios/".$nombreArchivo);

                if($key+1 == sizeof($nombreArchivos)){
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

            $ubicacion = "Subsidios/".$re_anio."-".$re_mes."-comprimido.zip";

        }else{
            foreach ($nombreArchivos as $key => $nombreArchivo) {
                $ubicacion = "Subsidios/".$nombreArchivo;
            }
        }


        $requestsalida = response()->json([
            "respuesta" => true,
            "urlDescargar" => $ubicacion
        ]);

        return $requestsalida;

    }

    public function ArmarExcelNc($array_generar_excel, $anio, $mes)
    {

        $hojas_clientes = $array_generar_excel['clientes'];

        $documento = new Spreadsheet();

        foreach ($hojas_clientes as $key => $excel) {
            if($key == 0){
                
                $hoja = $documento->getActiveSheet();
                $hoja->setTitle($excel['cliente']);

            }else{
                $worksheet2 = $documento->createSheet();
                $worksheet2->setTitle($excel['cliente']);

                $hoja = $documento->getSheet($key);
            }

            
    
            //DEFINICION DEL TAMAÑO DE LAS COLUMNAS Y FILAS
            $hoja->getColumnDimension('B')->setVisible(false);
            $hoja->getColumnDimension('A')->setWidth(6.45);
            $hoja->getColumnDimension('C')->setWidth(6);
            $hoja->getColumnDimension('D')->setWidth(25.39);
            $hoja->getColumnDimension('E')->setWidth(11);
            $hoja->getColumnDimension('F')->setWidth(43.69);
            $hoja->getColumnDimension('G')->setWidth(22);
            $hoja->getColumnDimension('H')->setWidth(20);
            $hoja->getColumnDimension('I')->setWidth(6.45);
            $hoja->getRowDimension('2')->setRowHeight(37.5);
            $hoja->getRowDimension('3')->setRowHeight(36.5);
            $hoja->getRowDimension('4')->setRowHeight(40.5);
            for ($i=5; $i <= 14 ; $i++) { 
                $hoja->getRowDimension("$i")->setRowHeight(31.5);
            }
            $hoja->getRowDimension('15')->setRowHeight(13.5);
            $hoja->getRowDimension('16')->setRowHeight(23.25);

            $r = 17;
            for ($conta = 0; $conta < sizeof($excel['facturas']) ; $conta++) { 
                $hoja->getRowDimension("$r")->setRowHeight(17.25);
                $r = $r + 1;
            }
            // $hoja->getRowDimension('60')->setRowHeight(11.25);
            // $hoja->getRowDimension('61')->setRowHeight(11.25);
            // $hoja->getRowDimension('62')->setRowHeight(11.25);
            // $hoja->getRowDimension('63')->setRowHeight(11.25);
            // $hoja->getRowDimension('64')->setRowHeight(9);
            // $hoja->getRowDimension('65')->setRowHeight(18);
            // $hoja->getRowDimension('66')->setRowHeight(23.25);
            // $hoja->getRowDimension('67')->setRowHeight(23.25);
            // $hoja->getRowDimension('68')->setRowHeight(23.25);
            //UNION DE COLUMNAS
            $hoja->mergeCells('C2:D2');
            $hoja->mergeCells('C3:D3');
            $hoja->mergeCells('E2:G3');
            $hoja->mergeCells('H2:H3');
            $hoja->mergeCells('C4:H4');
            for ($i=5; $i <= 14 ; $i++) { 
                $hoja->mergeCells("C$i:D$i");
                $hoja->mergeCells("E$i:H$i");
            }
            // $hoja->mergeCells('D56:F56'); //
            // $hoja->mergeCells('C58:E59'); //
            // $hoja->mergeCells('F58:F59'); //
            // $hoja->mergeCells('G58:H59'); //
            // $hoja->mergeCells('C60:E63'); //
            // $hoja->mergeCells('F60:F63'); //
            // $hoja->mergeCells('G60:H63'); //

            // $drawing = new Drawing();
            // $drawing->setName('Logo');
            // $drawing->setDescription('Logo');
            // $drawing->setPath('./images/logo.png');
            // $drawing->setCoordinates('C2:D3');
            // $drawing->setHeight(2.39, 'cm');

            $hoja->getStyle('C2:D3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');
            $hoja->getStyle('C2:D3')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);


            $hoja->getStyle('C2')->getFont()->setSize(32)->setName('Arial Nova');
            $hoja->getStyle('C3')->getFont()->setSize(5.8)->setName('Arial Nova');

            $hoja->getStyle('C2')->getFont()->getColor()->setARGB("FF44546A");
            $hoja->getStyle('C3')->getFont()->getColor()->setARGB("FF44546A");

            $hoja->setCellValue('C2', "Softys")
                    ->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_BOTTOM)->setWrapText(true);

            $hoja->setCellValue('C3', "INNOVANDO PARA TU CUIDADO")
                    ->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            


            $hoja->setCellValue('E2', "SOLICITUD DE EMISIÓN DE NOTA DE CRÉDITO FINANCIERA")
                    ->getStyle('E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

            $hoja->getStyle('E2')->getFont()->setSize(18)->setBold(true)->setName('Arial');
            $hoja->getStyle('E2:G3')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->getStyle('H2:H3')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C4','Por medio de la presente le solicitamos la generación de una Nota de Crédito Financiera al cliente de la referencia según datos adjuntos:')
                    ->getStyle('C4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C4')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('C4:H4')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C5','Solicitado por:')
                ->getStyle('C5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C5')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C5:D5')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E5', $array_generar_excel['solicitado'])
                ->getStyle('E5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E5')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('E5:H5')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C6','Canal:')
                ->getStyle('C6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C6')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C6:D6')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E6','20 - INSTITUCIONAL') // DUDA
                ->getStyle('E6')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E6')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('E6:H6')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C7','Fecha:')
                ->getStyle('C7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C7')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C7:D7')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E7', date('d-m-Y'))
                ->getStyle('E7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E7')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('E7:H7')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C8','Oficina de ventas:')
                ->getStyle('C8')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C8')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C8:D8')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E8', '1304')
                ->getStyle('E8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E8')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('E8:H8')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C9','Destinatario')
                ->getStyle('C9')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C9')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C9:D9')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E9', $excel['destinatario']." - ".$excel['cliente'])
                ->getStyle('E9')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E9')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('E9:H9')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C10','Cliente:')
                ->getStyle('C10')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C10')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C10:D10')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E10', $excel['solicitante']." - ".$excel['clihml']) //DUDA
                ->getStyle('E10')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E10')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('E10:H10')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C11','Valor venta:')
                ->getStyle('C11')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C11')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C11:D11')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E11', $excel['total'])
                ->getStyle('E11')
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

            $hoja->getStyle("E11")->getNumberFormat()
                            ->setFormatCode('#,##0.00');//LA COMA EN NUMEROS

            $hoja->getStyle('E11')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('E11:H11')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C12','Motivo de descuento:')
                ->getStyle('C12')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C12')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C12:D12')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E12', 'DESCUENTOS EN PRECIO')
                ->getStyle('E12')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E12')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('E12:H12')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C13','Detalle')
                ->getStyle('C13')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C13')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C13:D13')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E13', 'PRECIOS ESPECIALES (SUBSIDIO)')
                ->getStyle('E13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E13')->getFont()->setSize(14)->setName('Arial');
            $hoja->getStyle('E13:H13')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('C14','Correspondiente al mes')
                ->getStyle('C14')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('C14')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('C14:D14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E14', $array_generar_excel['mes'])
                ->getStyle('E14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E14')->getFont()->setSize(14)->setBold(true)->setName('Arial');
            $hoja->getStyle('E14:H14')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            //CONTORNO TABLA PRODUDCTOS
            $hoja->getStyle('C15:H15')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');
            $hoja->getStyle('C15')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
            $hoja->getStyle('H15')->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

            $i = 16;
            for ($conta=0; $conta < sizeof($excel['facturas']) ; $conta++) { 
                $hoja->getStyle("C$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');
                $hoja->getStyle("C$i")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
                $hoja->getStyle("H$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');
                $hoja->getStyle("H$i")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
                $i = $i+1;
            }
            $hoja->getStyle('C61:H61')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');
            $hoja->getStyle('C61')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
            $hoja->getStyle('H61')->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);


            //TABLA DE PRODUCTOS
            //CABECERA
            $hoja->setCellValue('D16', 'Factura de referencia')
                    ->getStyle('D16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('D16')->getFont()->setSize(10)->setBold(true)->setName('Arial');
            $hoja->getStyle('D16')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('E16', 'Material')
                    ->getStyle('E16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('E16')->getFont()->setSize(10)->setBold(true)->setName('Arial');
            $hoja->getStyle('E16')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('F16', 'Descripción')
                    ->getStyle('F16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('F16')->getFont()->setSize(10)->setBold(true)->setName('Arial');
            $hoja->getStyle('F16')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue('G16', 'Importe')
                    ->getStyle('G16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle('G16')->getFont()->setSize(10)->setBold(true)->setName('Arial');
            $hoja->getStyle('G16')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            //CONTENIDO
            $i = 18;
            $otros = 17;

            $ultimaFilaFacturas = 0;
            $ultimoTotalFactura = 0;
            $diferenciaTotal = 0;

            for ($cont=0; $cont < sizeof($excel['facturas']) ; $cont++) { 

                if($excel['facturas'][$cont]['sfsvalorizado'] >= 0.1){
                    $hoja->setCellValue("D$otros", $excel['facturas'][$cont]['fsifactura'])
                        ->getStyle("D$otros")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);
                    $hoja->getStyle("D$otros")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    
                    $hoja->setCellValue("E$otros", $excel['facturas'][$cont]['fdsmaterial'])
                            ->getStyle("E$otros")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                            ->setVertical(Alignment::VERTICAL_BOTTOM);
                    $hoja->getStyle("E$otros")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    
                    $hoja->setCellValue("F$otros", $excel['facturas'][$cont]['pronombre'])
                            ->getStyle("F$otros")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);
                    $hoja->getStyle("F$otros")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    
                    // $hoja->setCellValue("G$i", number_format($excel['facturas'][$cont]['sfsvalorizado'], 2))
                    //         ->getStyle("G$i")->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);

                    $hoja->setCellValue("G$otros", $excel['facturas'][$cont]['sfsvalorizado']);

                    $ultimaFilaFacturas = $otros;
                    $ultimoTotalFactura = $excel['facturas'][$cont]['sfsvalorizado'];

                    $hoja->getStyle("G$otros")->getNumberFormat()
                            ->setFormatCode('#,##0.00');//LA COMA EN NUMEROS
                
                    $hoja->getStyle("G$otros")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
    
                    $i = $i + 1;
                    $otros = $otros + 1;
                } else{
                    $diferenciaTotal = $excel['facturas'][$cont]['sfsvalorizado'] + $diferenciaTotal;
                }


            }


            $hoja->setCellValue("G$ultimaFilaFacturas", doubleval($ultimoTotalFactura) + doubleval($diferenciaTotal));
            
            $i = $i - 1;

            $hoja->setCellValue("D$i", '');
            $hoja->setCellValue("E$i", '');
            $hoja->setCellValue("F$i", '');
            $hoja->setCellValue("G$i", '');

            $i = $i + 1;

            $hoja->mergeCells("D$i:F$i");

            $hoja->setCellValue("D$i", 'TOTAL')
                ->getStyle("D$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_BOTTOM);

            $hoja->setCellValue("G$i", $excel['total']);
            $hoja->getStyle("G$i")->getNumberFormat()
                            ->setFormatCode('#,##0.00');//LA COMA EN NUMEROS

            $hoja->getStyle("D$i:F$i")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $hoja->getStyle("G$i:G$i")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $ultimoRow = $i + 1; 
            // $hoja->getStyle('C59:H59')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
            $hoja->getStyle("C17:C$ultimoRow")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);//borde izquierdo
            $hoja->getStyle("H17:H$ultimoRow")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);//borde derecho
            
            $i = $i + 2;
            $c = $i + 1;

            $hoja->mergeCells("C$i:E$c"); 
            $hoja->mergeCells("F$i:F$c"); 
            $hoja->mergeCells("G$i:H$c");
            // $hoja->mergeCells('C60:E63');

            //FOOTER
            $hoja->setCellValue("C$i", 'JEFE DE VENTAS')
                    ->getStyle("C$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle("C$i")->getFont()->setSize(10)->setBold(true)->setName('Arial');

            $d = $i+1;
            $hoja->getStyle("C$i:E$d")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue("F$i", 'GERENTE DIV.INSTITUCIONAL')
                    ->getStyle("F$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle("F$i")->getFont()->setSize(10)->setBold(true)->setName('Arial');
            $hoja->getStyle("F$i:F$d")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

            $hoja->setCellValue("G$i", 'GERENTE GENERAL')
                    ->getStyle("G$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $hoja->getStyle("G$i")->getFont()->setSize(10)->setBold(true)->setName('Arial');
            $hoja->getStyle("G$i:H$d")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            
            $i = $i +2;
            $d = $d +4;

            $c = $c + 4;

            $hoja->mergeCells("C$i:E$c");
            $hoja->mergeCells("F$i:F$c");
            $hoja->mergeCells("G$i:H$c");

            $hoja->getStyle("C$i:E$d")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $hoja->getStyle("F$i:F$d")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $hoja->getStyle("G$i:H$d")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            
            $i = $i + 5;

            $antesenviarsolicitud = $i - 1;
            $hoja->getRowDimension($antesenviarsolicitud)->setRowHeight(9);

            $hoja->setCellValue("C$i", 'Enviar la solicitud + sustento :')
                    ->getStyle("C$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            $hoja->getStyle("C$i")->getFont()->setSize(14)->setBold(true)->setName('Arial');
            
            $i = $i + 1;

            $hoja->setCellValue("C$i", 'Original: Contabilidad (Adm. vtas)')
                    ->getStyle("C$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            $hoja->getStyle("C$i")->getFont()->setSize(14)->setName('Arial');
            
            $i = $i + 1;
            
            $hoja->setCellValue("C$i", 'Copia: Contabilidad (Estudio Contable)')
                    ->getStyle("C$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            $hoja->getStyle("C$i")->getFont()->setSize(14)->setName('Arial');
            
            $i = $i + 1;

            $hoja->setCellValue("C$i", 'Copia: Distribución')
                    ->getStyle("C$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            $hoja->getStyle("C$i")->getFont()->setSize(14)->setName('Arial');   

            $i = $i - 4;
            $d = $i + 1;

            $hoja->getStyle("C$i:H$i")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');
            $hoja->getStyle("C$d:H$d")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFB4C6E7');

            $d = $d + 3;
            $hoja->getStyle("C$i:C$d")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
            $hoja->getStyle("H$i:H$d")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

            $i = $i + 2;

            $hoja->getStyle("C$i:H$d")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');
            $hoja->getStyle("C$d:H$d")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
            

    
        }
        
        //Creacion de otra hoja
        
        // $hoja->setCellValue('A1', 'GERENTE GENERAL');
        // $hoja->setCellValue('A2', 'GERENTE GENERAL2');

        //definir la hoja activa
        $hoja = $documento->setActiveSheetIndex(0);

                        
        $fileNameExcel= $anio."-".$mes."-".$array_generar_excel['excelnombre'].".xlsx";
        // $fileNameExcel= "NORTEA.xlsx";
        $writer = new Xlsx($documento);
        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // header('Content-Disposition: attachment; filename="'. urlencode($fileNameExcel).'"');
        // $writer->save('php://output');
        $writer->save("Subsidios/".$fileNameExcel);

        return $fileNameExcel;
    }

    
}
