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

    public function LimpiarSde()
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
                $nuevaDiferencia = $sfs->valorizado - $sfs->fdstreintaporciento;

                if($nuevaDiferencia > $mayorDiferencia){
                    $mayorDiferencia = $nuevaDiferencia;
                }

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

            //     $treintaData = floatval($treintaData);
            //     $valorizData = floatval($valorizData);

            //     if($treintaData == $valorizData){

            //     }else{

            //         if($treintaData >= $valorizData){

            //         }else{
            //             $treintaPorciento[] = array(
            //                 "fdsid"      => $sfs->fdsid,
            //                 "treinta"    => $sfs->fdstreintaporciento,
            //                 "valorizado" => $sfs->valorizado
            //             );
            //         }

            //     }

            // }

        }

        return $treintaPorciento;

    }

}
