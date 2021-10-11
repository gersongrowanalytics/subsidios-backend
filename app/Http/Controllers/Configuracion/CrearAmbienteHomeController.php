<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fecfechas;
use App\Models\areareasestados;
use App\Models\espestadospendientes;

class CrearAmbienteHomeController extends Controller
{
    public function CrearAmbiente(Request $request)
    {

        $re_fecfecha          = $request['re_fecfecha'];
        $re_fecmesabreviacion = $request['re_fecmesabreviacion'];
        $re_fecdianumero      = $request['re_fecdianumero'];
        $re_fecanionumero     = $request['re_fecanionumero'];
        $re_fecdiatexto       = $request['re_fecdiatexto'];
        $re_fecmestexto       = $request['re_fecmestexto'];
        $re_fecaniotexto      = $request['re_fecaniotexto'];
        // $re_fecmesabierto     = $request['re_fecmesabierto'];

        $re_soefectivo                    = $request['re_soefectivo'];
        $re_subsidiosaprobados            = $request['re_subsidiosaprobados'];
        $re_operacionessunat              = $request['re_operacionessunat'];
        $re_sellinefectivo                = $request['re_sellinefectivo'];
        $re_subsidiosreconocidomanual     = $request['re_subsidiosreconocidomanual'];
        $re_subsidiosreconocidoautomatico = $request['re_subsidiosreconocidoautomatico'];

        $fecAbierto = fecfechas::where('fecmesabierto', true)->first();

        if($fecAbierto){

            // $fecAbierto->fecmesabierto = false;
            $fecultimo = fecfechas::orderby('fecid', 'desc')->first();
            $pkfec = $fecultimo->fecid + 1;

            $areultimo = areareasestados::orderby('areid', 'desc')->first();
            $pkare = $areultimo->areid + 1;

            $espultimo = espestadospendientes::orderby('espid', 'desc')->first();
            $pkesp = $espultimo->espid + 1;

            $fecn = new fecfechas;
            $fecn->fecid = $pkfec;
            $fecn->fecfecha = $re_fecfecha;
            $fecn->fecmesabreviacion = $re_fecmesabreviacion;
            $fecn->fecdianumero = $re_fecdianumero;
            $fecn->fecanionumero = $re_fecanionumero;
            $fecn->fecdiatexto = $re_fecdiatexto;
            $fecn->fecmestexto = $re_fecmestexto;
            $fecn->fecaniotexto = $re_fecaniotexto;
            $fecn->fecmesabierto = true;
            if($fecn->save()){
                $aren = new areareasestados;
                $aren->areid = $pkare;
                $aren->fecid = $fecn->fecid;
                $aren->tprid = 1;
                $aren->areicono = "/Sistema/Modulos/Home/areas/iconoRevenue.png";
                $aren->arenombre = "Revenue";
                $aren->areporcentaje = "0";
                $aren->save();
                $areidRevenue = $aren->areid;
                $pkare = $pkare +1 ;

                $arendos = new areareasestados;
                $arendos->areid = $pkare;
                $arendos->fecid = $fecn->fecid;
                $arendos->tprid = 1;
                $arendos->areicono = "/Sistema/Modulos/Home/areas/iconoSac.png ";
                $arendos->arenombre = "SAC Sell In";
                $arendos->areporcentaje = "0";
                $arendos->save();
                $areidSacSI = $arendos->areid;
                $pkare = $pkare +1 ;

                $arentres = new areareasestados;
                $arentres->areid = $pkare;
                $arentres->fecid = $fecn->fecid;
                $arentres->tprid = 1;
                $arentres->areicono = "/Sistema/Modulos/Home/areas/iconoSac.png ";
                $arentres->arenombre = "SAC Sell Out";
                $arentres->areporcentaje = "0";
                $arentres->save();
                $areidSacSO = $arentres->areid;
                $pkare = $pkare +1 ;

                $arencuatro = new areareasestados;
                $arencuatro->areid = $pkare;
                $arencuatro->fecid = $fecn->fecid;
                $arencuatro->tprid = 1;
                $arencuatro->areicono = "/Sistema/Modulos/Home/areas/iconoSac.png ";
                $arencuatro->arenombre = "SAC Sell Out Detalle";
                $arencuatro->areporcentaje = "9090"; // EL PORCENTAJE DEBE SER 9090 YA QUE HAY UNA RESTRICCIÓN A ESTE PORCENTAJE EN EL FRONTEND
                $arencuatro->save();
                $areidSacSODetalle = $arencuatro->areid;
                $pkare = $pkare +1 ;

                // REVENUE
                $espn = new espestadospendientes;
                $espn->espid = $pkesp;
                $espn->fecid = $fecn->fecid;
                $espn->perid = null;
                $espn->areid = $areidRevenue;
                $espn->espfechaprogramado = $re_soefectivo;
                $espn->espchacargareal = null;
                $espn->espfechactualizacion = null;
                $espn->espbasedato = "Subsidio Aprobado (Plantilla)";
                $espn->espresponsable = "Maria Yauri";
                $espn->espdiaretraso = "0";
                $espn->esporden = "0";
                $espn->cliid = null;
                $espn->save();
                $pkesp = $pkesp + 1;

                $espn = new espestadospendientes;
                $espn->espid = $pkesp;
                $espn->fecid = $fecn->fecid;
                $espn->perid = null;
                $espn->areid = $areidRevenue;
                $espn->espfechaprogramado = $re_subsidiosaprobados;
                $espn->espchacargareal = null;
                $espn->espfechactualizacion = null;
                $espn->espbasedato = "Subsidio Aprobado (Plantilla)";
                $espn->espresponsable = "Maria Yauri";
                $espn->espdiaretraso = "0";
                $espn->esporden = "0";
                $espn->cliid = null;
                $espn->save();
                $pkesp = $pkesp + 1;
                // FIN DE REVENUE

                // SAC SELL IN
                $espn = new espestadospendientes;
                $espn->espid = $pkesp;
                $espn->fecid = $fecn->fecid;
                $espn->perid = null;
                $espn->areid = $areidSacSI;
                $espn->espfechaprogramado = $re_operacionessunat;
                $espn->espchacargareal = null;
                $espn->espfechactualizacion = null;
                $espn->espbasedato = "Operaciones Sunat";
                $espn->espresponsable = "SAC";
                $espn->espdiaretraso = "0";
                $espn->esporden = "0";
                $espn->cliid = null;
                $espn->save();
                $pkesp = $pkesp + 1;

                $espn = new espestadospendientes;
                $espn->espid = $pkesp;
                $espn->fecid = $fecn->fecid;
                $espn->perid = null;
                $espn->areid = $areidSacSI;
                $espn->espfechaprogramado = $re_sellinefectivo;
                $espn->espchacargareal = null;
                $espn->espfechactualizacion = null;
                $espn->espbasedato = "Sell In (Factura Efectiva)";
                $espn->espresponsable = "SAC";
                $espn->espdiaretraso = "0";
                $espn->esporden = "0";
                $espn->cliid = null;
                $espn->save();
                $pkesp = $pkesp + 1;
                // FIN SAC SELL IN

                // SAC SELL OUT

                $espn = new espestadospendientes;
                $espn->espid = $pkesp;
                $espn->fecid = $fecn->fecid;
                $espn->perid = null;
                $espn->areid = $areidSacSO;
                $espn->espfechaprogramado = $re_subsidiosreconocidoautomatico;
                $espn->espchacargareal = null;
                $espn->espfechactualizacion = null;
                $espn->espbasedato = "Subsidio Reconocido (Plantilla Automatico)";
                $espn->espresponsable = "SAC";
                $espn->espdiaretraso = "0";
                $espn->esporden = "0";
                $espn->cliid = null;
                $espn->save();
                $pkesp = $pkesp + 1;

                $espn = new espestadospendientes;
                $espn->espid = $pkesp;
                $espn->fecid = $fecn->fecid;
                $espn->perid = null;
                $espn->areid = $areidSacSO;
                $espn->espfechaprogramado = $re_subsidiosreconocidomanual;
                $espn->espchacargareal = null;
                $espn->espfechactualizacion = null;
                $espn->espbasedato = "Subsidio Reconocido (Plantilla Manual)";
                $espn->espresponsable = "SAC";
                $espn->espdiaretraso = "0";
                $espn->esporden = "0";
                $espn->cliid = null;
                $espn->save();
                $pkesp = $pkesp + 1;

                // FIN SELL OUT

                // SAC SELL OUT DETALLE
                
                $espn = new espestadospendientes;
                $espn->espid = $pkesp;
                $espn->fecid = $fecn->fecid;
                $espn->perid = null;
                $espn->areid = $areidSacSODetalle;
                $espn->espfechaprogramado = $re_subsidiosreconocidoautomatico;
                $espn->espchacargareal = null;
                $espn->espfechactualizacion = null;
                $espn->espbasedato = "Subsidio Reconocido (Plantilla Automatico)";
                $espn->espresponsable = "SAC";
                $espn->espdiaretraso = "0";
                $espn->esporden = "0";
                $espn->cliid = null;
                $espn->save();
                $pkesp = $pkesp + 1;
                
                // FIN SELL OUT DETALLE

                // re_soefectivo
                // re_subsidiosaprobados
                // re_operacionessunat
                // re_sellinefectivo
                // re_subsidiosreconocidomanual
                // re_subsidiosreconocidoautomatico


            }


        }else{

        }



    }
}