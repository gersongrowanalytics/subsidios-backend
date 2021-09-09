<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cliclientes;
use App\Models\zonzonas;
use App\Models\sdesubsidiosdetalles;

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
}
