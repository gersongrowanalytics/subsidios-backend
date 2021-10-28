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
                                            $query->where('sdesubsidiosdetalles.fecid', 1105);
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
                                            $query->where('sdesubsidiosdetalles.fecid', 1105);
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
                                        'fsipedido',
                                    )
                                    ->where('sfssubsidiosfacturassi.fecid', '1105')
                                    ->limit(50)
                                    ->get();
        
        $array = array();

        foreach($sfss as $sfs){

            $fsis = fsifacturassi::where('fsipedido', $sfs->fsipedido)
                                ->where('fsifactura', "!=", $sfs->fsifactura)
                                ->get();

            if(sizeof($fsis) > 0){
                $facturas = array();
                foreach($fsis as $fsi){
                    $facturas[] = array(
                        "fsiid" => $fsi->fsiid,
                        "factura" => $fsi->fsifactura,
                    );
                }
                $array[] = array(
                    "pedido" => $sfs->fsipedido,
                    "facturas" => $facturas
                );
            }

        }

        dd($array);
    }

}
