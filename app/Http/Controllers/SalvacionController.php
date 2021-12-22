<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cliclientes;
use App\Models\zonzonas;
use App\Models\sdesubsidiosdetalles;
use App\Models\fdsfacturassidetalles;
use App\Models\fsifacturassi;
use App\Models\perpersonas;
use App\Models\usuusuarios;
use App\Models\sfssubsidiosfacturassi;
use App\Models\ndsnotascreditossidetalles;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;
use Illuminate\Support\Facades\Hash;
use DB;

class SalvacionController extends Controller
{
    public function HabilitarZonas()
    {
        $clis = cliclientes::distinct('clizona')->get();

        foreach($clis as $cli){
            
            $zon = zonzonas::where('zonnombre', $cli->clizona)->first();

            if($zon){
                $cli->zonid = $zon->zonid;
            }else{
                $zonn = new zonzonas;
                $zonn->zonnombre = $cli->clizona;
                if($zonn->save()){
                    $cli->zonid = $zonn->zonid;
                }
            }

            $cli->update();
        }

    }

    public function ReinicarSubDtYReal($fecid)
    {

        $sdee = sdesubsidiosdetalles::where('fecid', $fecid)->update([
            "sdecantidadbultos" => 0,
            "sdemontoareconocer" => 0,
            "sdecantidadbultosreal" => 0,
            "sdemontoareconocerreal" => 0,
            "sdestatus" => null,
            "sdediferenciaahorro" => null,
        ]);
    }

    public function CambiarValidados($fecid)
    {

        $sdes = sdesubsidiosdetalles::where('fecid', $fecid)->get();

        foreach($sdes as $sde){
            $sdee = sdesubsidiosdetalles::find($sde->sdeid);
            if($sde->sdecantidadbultosreal > 0){
                $sdee->sdevalidado = "SIVALIDADOS";    
            }else{
                $sdee->sdevalidado = "NOVALIDADOS";
            }
            $sdee->update();
        }

    }

    public function EnviarCorreo(Request $request)
    {

        // perpersonas::create([
        //     "perid"                       => 12,
        //     "pernumerodocumentoidentidad" => "0000000",
        //     "pernombrecompleto"           => "Alberto Torres Trucios",
        //     "pernombre"                   => "Alberto",
        //     "perapellidopaterno"          => "Torres",
        //     "perapellidomaterno"          => "Trucios",
        // ]);

        // usuusuarios::create([
        //     "usuid"           => 11,
        //     "tpuid"           => 2,
        //     "perid"           => 12,
        //     "estid"           => 1,
        //     "usucodigo"       => "SAC-TORRES-01",
        //     "usuusuario"      => "atorres@softys.com",
        //     "usucorreo"       => "atorres@softys.com",
        //     "usucontrasenia"  => Hash::make('Alberto$$Torres$$321456'),
        //     "usutoken"        => "AlberIDMWZZwOPOR434561aqd11aPWOALSDpDLQW2ldrwke989230Torre",
        // ]);

        $url     = $request['url'];
        $usuario = $request['usuario'];
        $tipo    = $request['tipo'];
        $archivo = $request['archivo'];
        $correo  = $request['correo'];

        $data = [
            'archivo'      => $archivo, 
            "tipo"         => $tipo, 
            "usuario"      => $usuario,
            "url_archivo"  => $url,
            "correo"  => $correo,
        ];
        // dd($data);
        Mail::to($correo)->send(new MailCargaArchivoOutlook($data));

    }

    public function AsignarPedidoFacturas()
    {

        $fdss = fdsfacturassidetalles::where('fsiid', 0)->limit(500)->get();

        foreach($fdss as $fds){

            $fsie = fsifacturassi::where('fsipedido', $fds->fdspedido)->first();

            if($fsie){
                $fdse = fdsfacturassidetalles::find($fds->fdsid);
                $fdse->fsiid = $fsie->fsiid;
                $fdse->update();
            }

        }



    }

    public function LimpiarSde($fecid)
    {

        // $sdes = sdesubsidiosdetalles::where('fecid', 1104)->get();

        // foreach($sdes as $sde){
        //     $suma = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)->sum('sfsvalorizado');

        //     $sdee = sdesubsidiosdetalles::find($sde->sdeid);
        //     $sdee->sumsfsvalorizado = $suma;
        //     $sdee->update();

        // }
        


















        $otro = "";

        $zonas = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where(function ($query) use($otro) {
                                        // if($fechaInicio != null){
                                            // $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                            $query->where('sdesubsidiosdetalles.fecid', 1106);
                                        // }
                                    })
                                    ->distinct('cli.clizona')
                                    // ->orderBy('clizonacodigo', 'DESC')
                                    // ->where('sdestatus', '!=', null)
                                    ->get([
                                        'cli.clizona'
                                    ]);



        foreach($zonas as $posicionZon => $zon){

            $sdes = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->join('proproductos as pro', 'pro.proid', 'sdesubsidiosdetalles.proid')
                                    ->join('catcategorias as cat', 'cat.catid', 'pro.catid')
                                    ->join('fecfechas as fec', 'fec.fecid', 'sdesubsidiosdetalles.fecid')
                                    ->where('clizona', $zon['clizona'])
                                    ->where(function ($query) use($otro) {
                                        // if($fechaInicio != null){
                                            // $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                            $query->where('sdesubsidiosdetalles.fecid', 1106);
                                        // }
                                    })
                                    // ->orderBy('sdestatus' , 'DESC')
                                    ->orderBy('sdeterritorio' , 'ASC')
                                    ->orderBy('clihml' , 'ASC')
                                    ->orderBy('clisuchml' , 'ASC')
                                    ->orderBy('sdesubcliente' , 'DESC')
                                    ->orderBy('sdesector' , 'DESC')
                                    ->orderBy('sdecantidadbultosreal' , 'DESC')
                                    ->get([
                                        'sdesubsidiosdetalles.sdeid',
                                        'cli.cliid',
                                        'clizona',
                                        'clisuchml',
                                        'clihml as clinombre',
                                        // 'clinombre',
                                        'sdesubcliente',
                                        'catnombre',
                                        'propresentacion',
                                        'pro.proid',
                                        'prosku',
                                        'pronombre',
                                        'sdecantidadbultos',
                                        'sdemontoareconocer',
                                        'sdecantidadbultosreal',
                                        'sdemontoareconocerreal',
                                        'sdestatus',
                                        'sdediferenciaahorro',
                                        'sdebultosacordados',
                                        'fec.fecid',
                                        'fecfecha',
                                        // 'fsifecha as fecfecha',
                                        'sdependiente',
                                        'sdesac',
                                        'sdesector',
                                        'sdeterritorio',
                                        'sdevalidado',
                                        'clicodigoshipto',
                                        'sumsfsvalorizado'
                                    ]);

            foreach($sdes as $posicionSde => $sde){

                $suma = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)->sum('sfsvalorizado');

                $sdee = sdesubsidiosdetalles::where('sdeid', $sde->sdeid)->first();
                $sdee->sumsfsvalorizado = $suma;
                $sdee->update();

            }

        }












    }

    public function MostrarPedidosRepetidos()
    {
        $sfss = sfssubsidiosfacturassi::join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                    ->select(
                                        DB::raw("distinct(fsifactura) as fsifactura"),
                                        'fsi.fsiid',
                                        'sfssubsidiosfacturassi.fdsid',
                                        'fsipedido'
                                    )
                                    ->where('sfssubsidiosfacturassi.fecid', '1105')
                                    ->limit(50)
                                    ->get();
        
        $array = array();

        foreach($sfss as $sfs){

            $fsis = fsifacturassi::where('fsipedido', $sfs->fsipedido)
                                ->where('fsifactura', "!=", $sfs->fsifactura)
                                ->first();

            if($fsis){
                $fdss = fdsfacturassidetalles::where('fsiid', $sfs->fsiid)->get();
                foreach($fdss as $posicionFds => $fds){
                    if($fds->fdsid == $sfs->fdsid){

                        $array[] = array(
                            "cantidad" => sizeof($fdss),
                            "posicion" => $posicionFds
                        );
                        break;
                    }
                }
                // $facturas = array();
                // foreach($fsis as $fsi){
                //     $facturas[] = array(
                //         "fsiid" => $fsi->fsiid,
                //         "factura" => $fsi->fsifactura,
                //     );
                // }
                // $array[] = array(
                //     "pedido" => $sfs->fsipedido,
                //     "facturaori" => $sfs->fsifactura,
                //     "facturas" => $facturas
                // );
            }

        }

        dd($array);
    }

    public function AgregarDetalleFacturaSfs()
    {

        $sfss = sfssubsidiosfacturassi::join('fdsfacturassidetalles as fds', 'sfssubsidiosfacturassi.fdsid', 'fds.fdsid')
                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'sfssubsidiosfacturassi.fsiid')
                                        ->where('sfsfactura', null)
                                        ->limit(500)
                                        ->get([
                                            'sfsid',
                                            'fsi.fsiid',
                                            'fsifactura',
                                            'fsidestinatario',
                                            'fds.fdsid',
                                            'fdsmaterial'
                                        ]);


                                        // ->update([
                                        //     "sfssubsidiosfacturassi.fsiid" => 0,
                                        //     "sfssubsidiosfacturassi.fdsid" => 0
                                        // ]);

        foreach($sfss as $sfs){

            $sfse = sfssubsidiosfacturassi::find($sfs->sfsid);
            $sfse->sfsdestinatario = $sfs->fsidestinatario;
            $sfse->sfsmaterial = $sfs->fdsmaterial;
            $sfse->sfsfactura = $sfs->fsifactura;
            $sfse->update();
        }

        echo sizeof($sfss);

    }

    public function AsignarIdFdsFsiASfs()
    {
        
        $sfss = sfssubsidiosfacturassi::where('fdsid', 0)
                                        ->get();

        foreach($sfss as $sfs){

            $fds = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
                                        ->where('fdsmaterial', $sfs->sfsmaterial)
                                        ->where('fdsfactura', $sfs->sfsfactura)
                                        ->where('fsidestinatario', $sfs->sfsdestinatario)
                                        ->first([
                                            'fdsfacturassidetalles.fdsid',
                                            'fdsfacturassidetalles.fsiid'
                                        ]);

            if($fds){

                $sfse = sfssubsidiosfacturassi::find($sfs->sfsid);
                $sfse->fsiid = $fds->fsiid;
                $sfse->fdsid = $fds->fdsid;
                $sfse->update();

            }else{
                echo "SFSID :".$sfs->sfsid.'<br>';
                echo "MATERIAL :".$sfs->sfsmaterial.'<br>';
                echo "FACTURA :".$sfs->sfsfactura.'<br>';
                echo "DEST :".$sfs->sfsdestinatario.'<br>';
                echo '<br>';
                echo '<br>';
            }
        }

        echo $sfss;

    }

    public function CorregirFechasFacturas($fecid)
    {

        // $fdss = fdsfacturassidetalles::join('fsifacturassi as fsi', 'fsi.fsiid', 'fdsfacturassidetalles.fsiid')
        //                                 ->join('fsi')
        //                                 ->where('fsi.fecid', $fecid)
        //                                 ->get([
        //                                     'fdsfacturassidetalles.fdsid',
        //                                     'fdsfacturassidetalles.fsiid',
        //                                     'fsifecha'
        //                                 ]);

        $fsis = fsifacturassi::join('fecfechas as fec', 'fec.fecid', 'fsifacturassi.fecid')
                                ->where('fsi.fecid', $fecid)
                                ->limit()
                                ->get([
                                    'fsifacturassi.fsiid',
                                    'fsifecha',
                                    'fecanionumero',
                                    'fecmesnumero'
                                ]);


        foreach($fsis as $fsi){

            


        }

    }

    public function AsignarBultosAcidos($fecid)
    {

        $sdes = sdesubsidiosdetalles::where($fecid)
                                        ->where('sdeaprobado', true)
                                        ->get();

        foreach($sdes as $sde){

            $sde = sdesubsidiosdetalles::find($sde->sdeid);
            $sde->sdebultosnoreconocido = 0;
            $sde->sdebultosacido = $sde->sdecantidadbultosreal;
            $sde->sdemontoacido = $sde->sdemontoareconocerreal;
            $sde->update();
        }

    }

    public function TreintaPorCientoSfs($fecid)
    {

        $sfss = sfssubsidiosfacturassi::join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                        ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                        ->where('sfsvalorizado', '>', 1)
                                        ->select(
                                            DB::raw("SUM(sfsvalorizado) as valorizado"),
                                            'fdstreintaporciento',
                                            'sfssubsidiosfacturassi.fdsid'
                                        )
                                        ->groupBy('sfssubsidiosfacturassi.fdsid')
                                        ->groupBy('fdstreintaporciento')
                                        ->get();
        
        $treintaPorciento = array();

        $mayorDiferencia = 0;

        foreach ($sfss as $key => $sfs) {

            if($sfs->fdstreintaporciento >= $sfs->valorizado){

            }else{

                // $valorizado = number_format($sfs->valorizado, 10);
                // $treinta    = number_format($sfs->fdstreintaporciento, 10);

                $valorizado = doubleval($sfs->valorizado);
                $treinta = doubleval($sfs->fdstreintaporciento);

                $nuevaDiferencia = $valorizado - $treinta;

                if($nuevaDiferencia > $mayorDiferencia){
                    $mayorDiferencia = $nuevaDiferencia;
                }

                // $sfsa = sfssubsidiosfacturassi::where('fdsid', $sfs->fdsid)->first();
                // $sfsa->sfsvalorizado = $sfsa->sfsvalorizado - $nuevaDiferencia;
                // $sfsa->update();

                // $sdee = sdesubsidiosdetalles::where('sdeid', $sfsa->sdeid)->first();
                // $sdee->sdependiente = true;
                // $sdee->update();

                $treintaPorciento[] = array(
                    "fdsid"      => $sfs->fdsid,
                    "treinta"    => $sfs->fdstreintaporciento,
                    "valorizado" => $sfs->valorizado,
                    "diferencia" => $sfs->valorizado - $sfs->fdstreintaporciento,
                    "mayor" => $mayorDiferencia,
                );
                
            }

            // $valSfsResta = $sfs->valorizado - 1;
            // $valSfsSuma  = $sfs->valorizado + 1;

            // if($sfs->fdstreintaporciento >= $valSfsResta && $sfs->fdstreintaporciento <= $valSfsSuma){

            //     $stringTreinta = strval($sfs->fdstreintaporciento);
            //     $stringValoriz = strval($sfs->valorizado);

            //     $porcionesTreinta = explode(".", $stringTreinta);
            //     $porcionesValoriz = explode(".", $stringValoriz);

            //     if(isset($porcionesTreinta[1])){
            //         $decimalesTreinta = $porcionesTreinta[1];
            //     }else{
            //         $decimalesTreinta = "0";
            //     }

            //     if(isset($porcionesValoriz[1])){
            //         $decimalesValoriz = $porcionesValoriz[1];
            //     }else{
            //         $decimalesValoriz = "0";
            //     }

            //     if(strlen($decimalesTreinta) >= 2){
            //         $treintaData = $porcionesTreinta[0].".".$decimalesTreinta[0].$decimalesTreinta[1];
            //     }else{
            //         $treintaData = $porcionesTreinta[0].".".$decimalesTreinta[0];
            //     }
                
            //     if(strlen($decimalesValoriz) >= 2){
            //         $valorizData = $porcionesValoriz[0].".".$decimalesValoriz[0].$decimalesValoriz[1];
            //     }else{
            //         $valorizData = $porcionesValoriz[0].".".$decimalesValoriz[0];
            //     }

            //     $treintaData = doubleval($treintaData);
            //     $valorizData = doubleval($valorizData);

            //     if($treintaData == $valorizData){

            //     }else{

            //         if($treintaData >= $valorizData){

            //         }else{

            //             // $nuevaDiferencia = $treintaData - $valorizData;
            //             $nuevaDiferencia = $valorizData - $treintaData;

            //             // $sfsa = sfssubsidiosfacturassi::where('fdsid', $sfs->fdsid)->first();
            //             // $sfsa->sfsvalorizado = $sfsa->sfsvalorizado - $nuevaDiferencia;
            //             // $sfsa->update();

            //             // $sdee = sdesubsidiosdetalles::where('sdeid', $sfsa->sdeid)->first();
            //             // $sdee->sdependiente = true;
            //             // $sdee->update();

            //             if($nuevaDiferencia > $mayorDiferencia){
            //                 $mayorDiferencia = $nuevaDiferencia;
            //             }

            //             $treintaPorciento[] = array(
            //                 "fdsid"      => $sfs->fdsid,
            //                 "treinta"    => $sfs->fdstreintaporciento,
            //                 "valorizado" => $sfs->valorizado,
            //                 "diferencia" => $sfs->valorizado - $sfs->fdstreintaporciento,
            //                 "mayor" => $mayorDiferencia,
            //             );
            //         }

            //     }

            // }

        }

        // QUITAR DE PENDIENTES A LOS SUBSIDIOS QUE TENGAN UN PENDIENTE DE 0

        // $logsPendientes = array();

        // $sdes = sdesubsidiosdetalles::where('sdependiente', true)
        //                             ->where('fecid', $fecid)
        //                             ->get([
        //                                 'sdeid',
        //                                 'sdemontoacido'
        //                             ]);

        // foreach ($sdes as $key => $sde) {
            
        //     $sumSfs = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)
        //                                     ->sum('sfsvalorizado');

        //     $estado = "NO ES";
        //     $diferencia = $sde->sdemontoacido - $sumSfs;
            
        //     if($diferencia < 1){
                
        //         if($diferencia <= 0.09){
        //             $estado = "SI ES";
        //             // $sdee = sdesubsidiosdetalles::find($sde->sdeid);
        //             // $sdee->sdependiente = false;
        //             // $sdee->update();
        //         }

        //         $logsPendientes[] = array(
        //             "SDEID" => $sde->sdeid,
        //             "SUMA_SFS" => $sumSfs,
        //             "MONTO_ACIDO" => $sde->sdemontoacido,
        //             "DIFERENCIA" => $diferencia,
        //             "ESTADO" => $estado
        //         );
        //     }

        // }



        


        // return $logsPendientes;
        return $treintaPorciento;

    }

    public function AlertaEstadoFacturasAsignadas($fecid)
    {
        
        $logs = array(
            "SUNAT" => [],
            "ANULADO" => []
        );

        $sfss = sfssubsidiosfacturassi::join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                        ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                        ->get([
                                            'sfssubsidiosfacturassi.sfsid',
                                            'fsi.fsiid',
                                            'fds.fdsid',
                                            'fsisunataprobado',
                                            'fdsanulada',
                                            'fsifactura'
                                        ]);

        foreach ($sfss as $key => $sfs) {
            
            if($sfs->fsisunataprobado == 0){
                $logs["SUNAT"][] = "La factura: ".$sfs->fsifactura." con FSIID: ".$sfs->fsiid;
            }
            
            if($sfs->fdsanulada == 1){
                $logs["ANULADO"][] = "La factura: ".$sfs->fsifactura." con FDSID: ".$sfs->fdsid;
            }
        }


        $sdes = sdesubsidiosdetalles::where('fecidregularizado', $fecid)
                                        ->get(['sdeid']);

        foreach ($sdes as $key => $sde) {
            
            $sfss = sfssubsidiosfacturassi::join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                        ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                        ->where('sfssubsidiosfacturassi.sdeid', $sde->sdeid)
                                        ->get([
                                            'sfssubsidiosfacturassi.sfsid',
                                            'fsi.fsiid',
                                            'fds.fdsid',
                                            'fsisunataprobado',
                                            'fdsanulada',
                                            'fsifactura'
                                        ]);

            foreach ($sfss as $key => $sfs) {
            
                if($sfs->fsisunataprobado == 0){
                    $logs["SUNAT"][] = "La factura: ".$sfs->fsifactura." con FSIID: ".$sfs->fsiid;
                }
                
                if($sfs->fdsanulada == 1){
                    $logs["ANULADO"][] = "La factura: ".$sfs->fsifactura." con FDSID: ".$sfs->fdsid;
                }
            }

        }

        dd($logs);
        
    }

    public function AlertaAsignacionFacturas($fecid)
    {

        $logs = array(
            array(
                "sfsid" => "0",
                "cliFDS" => 0,
                "cliSDE" => 0
            )
        );

        $sfss = sfssubsidiosfacturassi::join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                        ->join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                        ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                        ->get([
                                            'sfsid',
                                            'fds.cliid as cliFDS',
                                            'sde.cliid as cliSDE'
                                        ]);

        foreach ($sfss as $key => $sfs) {
            
            if($sfs->cliFDS == $sfs->cliSDE){

            }else{
                
                
                $cliSelecFDS = cliclientes::where('cliid', $sfs->cliFDS)->first();
                $cliSelecSDE = cliclientes::where('cliid', $sfs->cliSDE)->first();

                if($cliSelecFDS->clinombre == $cliSelecSDE->clinombre){

                }else{
                    $logs[] = array(
                        "sfsid"  => $sfs->sfsid,
                        "cliFDS" => $sfs->cliFDS,
                        "cliNombFDS" => $cliSelecFDS->clinombre,
                        "cliSDE" => $sfs->cliSDE,
                        "cliNombSDE" => $cliSelecSDE->clinombre
                    );
                }

            }

        }

        return dd($logs);

    }

    public function ValidarNcAsignadas($fecid)
    {

        $sfss = sfssubsidiosfacturassi::join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                    ->join('sdesubsidiosdetalles as sde', 'sde.sdeid', 'sfssubsidiosfacturassi.sdeid')
                                    ->join('cliclientes as cli', 'cli.cliid', 'sde.cliid')
                                    ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                    ->distinct('sfssubsidiosfacturassi.fdsid')
                                    ->get([
                                        'sfssubsidiosfacturassi.sfsid',
                                        'sfssubsidiosfacturassi.fdsid',
                                        'fdspedidooriginal',
                                        'sfsvalorizado',
                                        'fdstreintaporciento',
                                        'cli.cliid',
                                        'clizona',
                                        'fdsmaterial'
                                    ]);
        $logs = array(
            array()
        );

        $logsZonas = array(
            "DIRECTOS" => 0,
            "E COMMERCE" => 0,
            "ESTADO" => 0,
            "HUNTING" => 0,
            "LIMA" => array(
                "total" => 0,
                "cantidad" => 0,
                "clientes" => [],
                "numeroclientes" => 0
            ),
            "MASIVO" => 0,
            "No Aplica" => 0,
            "OTROS" => 0,
            "PROVINCIA" => array(
                "total" => 0,
                "cantidad" => 0,
                "clientes" => [],
                "numeroclientes" => 0
            ),
            "RETAIL" => 0,
            "VENTA INTERNA" => 0
        );

        $impacto = 0;

        foreach ($sfss as $key => $sfs) {
            
            $sumaNds = ndsnotascreditossidetalles::where('ndspedidooriginal', $sfs->fdspedidooriginal)
                                                ->where('ndsmaterial', $sfs->fdsmaterial)
                                                ->sum('ndsvalorneto');
            if($sumaNds > 0){
                $sumaNds = 0;
            }else{
                $sumaNds = abs($sumaNds);
            }

            $nuevoSaldo = $sfs->fdstreintaporciento - $sumaNds;

            if($nuevoSaldo < $sfs->sfsvalorizado){
                if($nuevoSaldo == $sfs->sfsvalorizado){

                }else{
                    if($sfs->sfsvalorizado > 1){
                    
                        $logs[] = array(
                            "treinta"    => $sfs->fdstreintaporciento,
                            "nds"        => $sumaNds,
                            "asignado"   => $sfs->sfsvalorizado,
                            "nuevoSaldo" => $nuevoSaldo
                        );
    
                        $ssaldo = abs($nuevoSaldo);
    
                        // $impacto = $ssaldo - $sfs->sfsvalorizado; 
        
                        if($nuevoSaldo < 0){
    
                        }
    
    
                        if($sfs->clizona == "LIMA"){
                            $logsZonas["LIMA"]['cantidad'] = $logsZonas["LIMA"]['cantidad'] + 1;
                            if($nuevoSaldo < 0){
                                $logsZonas["LIMA"]['total'] = $logsZonas["LIMA"]['total'] + $sfs->sfsvalorizado;
                            }
    
                            $encontroCliente = false;
    
                            foreach ($logsZonas["LIMA"]['clientes'] as $key => $value) {
                                
                                if( $value == $sfs->cliid){
                                    $encontroCliente = true;
                                }
    
                            }
    
                            if($encontroCliente == false){
                                $logsZonas["LIMA"]['clientes'][] = $sfs->cliid;
                                $logsZonas["LIMA"]['numeroclientes'] = $logsZonas["LIMA"]['numeroclientes'] + 1;
                            }
    
                        }else if($sfs->clizona == "PROVINCIA"){
                            $logsZonas["PROVINCIA"]['cantidad'] = $logsZonas["PROVINCIA"]['cantidad'] + 1;
                            if($nuevoSaldo < 0){
                                $logsZonas["PROVINCIA"]['total'] = $logsZonas["PROVINCIA"]['total'] + $sfs->sfsvalorizado;
                            }
    
                            $encontroCliente = false;
    
                            foreach ($logsZonas["PROVINCIA"]['clientes'] as $key => $value) {
                                
                                if( $value == $sfs->cliid){
                                    $encontroCliente = true;
                                }
    
                            }
    
                            if($encontroCliente == false){
                                $logsZonas["PROVINCIA"]['clientes'][] = $sfs->cliid;
                                $logsZonas["PROVINCIA"]['numeroclientes'] = $logsZonas["PROVINCIA"]['numeroclientes'] + 1;
                            }
                        }
    
                        
                    }
                }
            }

        }

        // dd($logsZonas);
        dd($logs);
        // echo $impacto;
        

    }

    public function MostrarSunatXMes()
    {

        $fecs = fsifacturassi::join('fecfechas as fec', 'fec.fecid', 'fsifacturassi.fecid')
                            ->distinct('fecid')
                            ->get([
                                'fec.fecid',
                                'fecanionumero',
                                'fecmesabreviacion'
                            ]);

        $logs = array();

        foreach ($fecs as $key => $fec) {
            
            $countfsis = fsifacturassi::where('fecid', $fec->fecid)
                                    ->where('fsisunataprobado', false)
                                    ->count();

            $logs[] = array(
                "fecid" => $fec->fecid,
                "mes"=> $fec->fecanionumero,
                "anio"=> $fec->fecmesabreviacion,
                "numero" => $countfsis
            );

        }

        dd($logs);


    }

    public function AlertaClientesBloqueados($fecid)
    {
        
        $sfss = sfssubsidiosfacturassi::join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                    ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                    ->where('sfssubsidiosfacturassi.fecid', $fecid)
                                    ->get([
                                        'sfssubsidiosfacturassi.sfsid',
                                        'fds.fdsid',
                                        'fsi.fsiid',
                                        'fsisolicitante'
                                    ]);
        
        $logs = array();

        foreach ($sfss as $key => $sfs) {
            
            $cli = cliclientes::where('clicodigoshipto', $sfs->fsisolicitante)->first();

            if($cli){
                if($cli->clibloqueado == true){
                    $logs[] = array(
                        "fdsid" => $sfs->fdsid,
                        "sfsid" => $sfs->sfsid,
                        'fsisolicitante' => $sfs->fsisolicitante
                    );
                }else{

                }
            }


        }

        $sdes = sdesubsidiosdetalles::where('fecidregularizado', $fecid)
                                        ->get(['sdeid']);

        foreach ($sdes as $key => $sde) {
            
            $sfss = sfssubsidiosfacturassi::join('fdsfacturassidetalles as fds', 'fds.fdsid', 'sfssubsidiosfacturassi.fdsid')
                                    ->join('fsifacturassi as fsi', 'fsi.fsiid', 'fds.fsiid')
                                    ->where('sfssubsidiosfacturassi.sdeid', $sde->sdeid)
                                    ->get([
                                        'sfssubsidiosfacturassi.sfsid',
                                        'fds.fdsid',
                                        'fsi.fsiid',
                                        'fsisolicitante'
                                    ]);

            foreach ($sfss as $key => $sfs) {
            
                $cli = cliclientes::where('clicodigoshipto', $sfs->fsisolicitante)->first();

                if($cli){
                    if($cli->clibloqueado == true){
                        $logs[] = array(
                            "fdsid" => $sfs->fdsid,
                            "sfsid" => $sfs->sfsid,
                            'fsisolicitante' => $sfs->fsisolicitante
                        );
                    }else{

                    }
                }


            }

        }

        dd($logs);
    }

    public function AlertaRestarMontoSubsidiarXMontoSubsidiado($fecid)
    {

        $logs = array(
        );

        $nuevoLogs = array();

        $sdes = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->where('fecid', $fecid)
                                    ->get(['sdeid', 'sdemontoacido', 'clinombre', 'clizona']);

        foreach ($sdes as $key => $sde) {
            
            $sumaSfs = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)
                                                ->sum('sfsvalorizado');

            $diferencia = $sde->sdemontoacido - $sumaSfs;

            // if($diferencia > 0.005){
            if($diferencia != 0){

                $encontroCliente = false;

                foreach ($logs as $key => $log) {
                    if($log['cliente'] == $sde->clinombre){
                        $logs[$key]['diferencia'] = $log['diferencia'] + $diferencia;
                        $encontroCliente = true;
                    }

                }

                if($encontroCliente == false){
                    $logs[] = array(
                        "sdeid" => $sde->sdeid,
                        "clizona" => $sde->clizona,
                        "cliente" => $sde->clinombre,
                        "montoacido" => $sde->sdemontoacido,
                        "valorizado" => $sumaSfs,
                        "diferencia" => $diferencia
                    );
                }

            }else if($diferencia < -0.009){
                $logsNegativos[] = array(
                    "sdeid" => $sde->sdeid,
                    "clizona" => $sde->clizona,
                    "cliente" => $sde->clinombre,
                    "montoacido" => $sde->sdemontoacido,
                    "valorizado" => $sumaSfs,
                    "diferencia" => $diferencia
                );
            }
        }

        foreach ($logs as $key => $log) {

            $nuevaDiferencia = number_format($log['diferencia'], 2);

            if($nuevaDiferencia != 0.00 && $log['cliente'] != "DERO SERV. GENERALES S.R.L."){
                $nuevoLogs[] = array(
                    "sdeid"      => $log['sdeid'],
                    "clizona"    => $log['clizona'],
                    "cliente"    => $log['cliente'],
                    "montoacido" => $log['montoacido'],
                    "valorizado" => $log['valorizado'],
                    "diferencia" => $nuevaDiferencia
                );
            }
        }



        return dd($nuevoLogs);

    }

}


// {
//     "sdeid": 103598,
//     "montoacido": 474.33,
//     "valorizado": 474.24,
//     "diferencia": 0.08999999999997499
//     },

// {
// "sdeid": 104240,
// "montoacido": 6.31238627584,
// "valorizado": 6.255,
// "diferencia": 0.05738627583999989
// },

// {
//     "sdeid": 104257,
//     "montoacido": 275.312,
//     "valorizado": 275.252,
//     "diferencia": 0.060000000000002274
//     },

// {
// "sdeid": 104280,
// "montoacido": 443.55530323864,
// "valorizado": 443.50530323864,
// "diferencia": 0.05000000000001137
// },

// {
//     "sdeid": 104356,
//     "montoacido": 180.448,
//     "valorizado": 180.37,
//     "diferencia": 0.07800000000000296
//     },