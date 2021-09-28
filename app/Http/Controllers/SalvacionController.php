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
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;

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

        perpersonas::create([
            "perid"                       => 11,
            "pernumerodocumentoidentidad" => "0000000",
            "pernombrecompleto"           => "Julian Cruz",
            "pernombre"                   => "Julian",
            "perapellidopaterno"          => "Cruz",
            "perapellidomaterno"          => "",
        ]);

        usuusuarios::create([
            "usuid"           => 10,
            "tpuid"           => 2,
            "perid"           => 11,
            "estid"           => 1,
            "usucodigo"       => "SacJulian-08",
            "usuusuario"      => "jcruzg@softys.com",
            "usucorreo"       => "jcruzg@softys.com",
            "usucontrasenia"  => Hash::make('Julian$$Cruz$$928134'),
            "usutoken"        => "JulianpIDMWZZwOPOR439asd11aPWOALSDQ2dkka2ldrwke989230CuzdlaI",
        ]);

        // $url     = $request['url'];
        // $usuario = $request['usuario'];
        // $tipo    = $request['tipo'];
        // $archivo = $request['archivo'];
        // $correo  = $request['correo'];

        // $data = [
        //     'archivo'      => $archivo, 
        //     "tipo"         => $tipo, 
        //     "usuario"      => $usuario,
        //     "url_archivo"  => $url,
        //     "correo"  => $correo,
        // ];
        // // dd($data);
        // Mail::to($correo)->send(new MailCargaArchivoOutlook($data));

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

}
