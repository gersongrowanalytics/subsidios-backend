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
use App\Models\csoclientesso;
use App\Models\tictipocambios;
use App\Models\cbucostosbultos;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailCargaArchivoOutlook;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        //     "perid"                       => 14,
        //     "pernumerodocumentoidentidad" => "0000000",
        //     "pernombrecompleto"           => "Valeria Romero Elías",
        //     "pernombre"                   => "Valeria",
        //     "perapellidopaterno"          => "Romero",
        //     "perapellidomaterno"          => "Elías",
        // ]);

        // usuusuarios::create([
        //     "usuid"           => 13,
        //     "tpuid"           => 2,
        //     "perid"           => 14,
        //     "estid"           => 1,
        //     "usucodigo"       => "SacValeria-09",
        //     "usuusuario"      => "vromeroe@softys.com",
        //     "usucorreo"       => "vromeroe@softys.com",
        //     "usucontrasenia"  => Hash::make('Valeria$$Romero$$82123'),
        //     "usutoken"        => "ValeriaIDMWZZwOPOR439SKSZXXZAOPALSDQ2dkka2ldrwke989230CRomero",
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
                                    ->where(function ($query) use($otro, $fecid) {
                                        // if($fechaInicio != null){
                                            // $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                            $query->where('sdesubsidiosdetalles.fecid', $fecid);
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
                                    ->where(function ($query) use($otro, $fecid) {
                                        // if($fechaInicio != null){
                                            // $query->whereBetween('fecfecha', [$fechaInicio, $fechaFinal]);
                                            $query->where('sdesubsidiosdetalles.fecid', $fecid);
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
        $sdeesEditados = [];

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

                $treintaPorciento[] = array(
                    "fdsid"      => $sfs->fdsid,
                    "treinta"    => $sfs->fdstreintaporciento,
                    "valorizado" => $sfs->valorizado,
                    "diferencia" => $sfs->valorizado - $sfs->fdstreintaporciento,
                    "mayor" => $mayorDiferencia,
                );
                
            }

        }

        
        // foreach ($treintaPorciento as $key => $data) {            

        //     $sfse = sfssubsidiosfacturassi::where('fdsid', $data['fdsid'])
        //                                     ->where('fecid', $fecid)
        //                                     ->first();

        //     if($sfse){

        //         $sfse->sfsvalorizado = $sfse->sfsvalorizado - $data['diferencia'];
        //         if($sfse->update()){

        //             $sdeesEditados[] = $sfse->sdeid;

        //             // $sdee = sdesubsidiosdetalles::where($sfse->sdeid)->first();

        //             // if($sdee){

        //             //     $sdee->sdependiente = true;
        //             //     $sdee->update();

        //             // }else{

        //             // }

        //         }

        //     }else{

        //     }

        // }


        $requestsalida = response()->json([
            "treintaPorciento" => $treintaPorciento,
            "sdees"  => $sdeesEditados,
        ]);

        return $requestsalida;

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
                                        'fsisolicitante',
                                        'fsidestinatario'
                                    ]);
        
        $logs = array();

        foreach ($sfss as $key => $sfs) {
            
            $cli = cliclientes::where('clicodigoshipto', $sfs->fsisolicitante)->first();

            if($cli){
                if($cli->clibloqueado == true){
                    $logs[] = array(
                        "fdsid" => $sfs->fdsid,
                        "sfsid" => $sfs->sfsid,
                        'fsisolicitante' => $sfs->fsisolicitante,
                        "tramo" => "FSI SOLICITANTE"
                    );
                }else{

                }
            }

            $cli = cliclientes::where('clicodigoshipto', $sfs->fsidestinatario)->first();
            if($cli){
                if($cli->clibloqueado == true){
                    $logs[] = array(
                        "fdsid" => $sfs->fdsid,
                        "sfsid" => $sfs->sfsid,
                        'fsidestinatario' => $sfs->fsidestinatario,
                        "tramo" => "FSI DESTINATARIO"
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
                                        'fsisolicitante',
                                        'fsidestinatario'
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

                $cli = cliclientes::where('clicodigoshipto', $sfs->fsidestinatario)->first();
                if($cli){
                    if($cli->clibloqueado == true){
                        $logs[] = array(
                            "fdsid" => $sfs->fdsid,
                            "sfsid" => $sfs->sfsid,
                            'fsisolicitante' => $sfs->fsidestinatario
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
                        $logs[$key]['montoacido'] = $log['montoacido'] + $sde->sdemontoacido;
                        $logs[$key]['valorizado'] = $log['valorizado'] + $sumaSfs;
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

            if($log['cliente'] == "MENDOZA SANDOVAL ROSA OTILIA"){
                $nuevoLogs[] = array(
                    "sdeid"      => $log['sdeid'],
                    "clizona"    => $log['clizona'],
                    "cliente"    => $log['cliente'],
                    "montoacido" => $log['montoacido'],
                    "valorizado" => $log['valorizado'],
                    "diferencia" => number_format($log['diferencia'], 4)
                );
            }else if($nuevaDiferencia != 0.00 && $log['cliente'] != "DERO SERV. GENERALES S.R.L."){
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

    public function AlertaValidarDiferenciaSOSI($fecid)
    {

        $sdes = sdesubsidiosdetalles::join('cliclientes as cli', 'cli.cliid', 'sdesubsidiosdetalles.cliid')
                                    ->where('fecid', $fecid)
                                    ->get(['sdeid', 'sdemontoacido', 'clinombre', 'clizona']);
        $logs = array();
        $sdeis = array();
        $diferenciatotal = 0;

        foreach ($sdes as $key => $sde) {
            
            $sumaSfs = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)
                                                ->sum('sfsvalorizado');

            $diferencia = doubleval($sde->sdemontoacido) - doubleval($sumaSfs);

            if(doubleval($diferencia) > 0.1){
                $diferenciatotal = doubleval($diferenciatotal) + doubleval($diferencia);
                $logs[] = array(
                    "cliente" => $sde->clinombre,
                    "dif."    => $diferencia,
                    "montoSO" => $sde->sdemontoacido,
                    "montoSI" => $sumaSfs,
                    "sdeid"   => $sde->sdeid
                );
                $sdeis[] = $sde->sdeid;
            }
        }

        $requestsalida = response()->json([
            "data" => $logs,
            "difTotal" => $diferencia,
            "sdeis" => $sdeis,
        ]);

        return $requestsalida;
    }

    public function ObtenerSubsidiosPendientes($fecid)
    {

        $sdes = sdesubsidiosdetalles::where('sdeaprobado', true)
                                    ->where('sdemontoareconocerreal', '!=',0)
                                    ->where('fecid', $fecid)
                                    ->get();


        $logs = array();

        foreach ($sdes as $key => $sde) {
            
            $sumSfs = sfssubsidiosfacturassi::where('sdeid', $sde->sdeid)
                                        ->where('fecid', $fecid)
                                        ->sum('sfsvalorizado');

            $diferencia = doubleval($sde->sdemontoacido) - doubleval($sumSfs);

            if($diferencia > 0){
                $logs[] = array(
                    "sdeid" => $sde->sdeid,
                    "sdemontoacido" => $sde->sdemontoacido,
                    "sumSfs" => $sumSfs,
                    "diferencia" => $diferencia
                );
            }
                

        }



        return dd($logs);




    }

    public function AsignarCsoidASubsidios($fecid)
    {
        $logs = array();

        $sdes = sdesubsidiosdetalles::where('fecid', $fecid)
                                    ->get();

        foreach ($sdes as $key => $sde) {
            
            $cso = csoclientesso::where('csocoddestinatario', $sde->sdecodigodestinatario)
                                    ->where('csorucsubcliente', $sde->sderucsubcliente)
                                    ->first();

            if($cso){

                $sdee = sdesubsidiosdetalles::find($sde->sdeid);
                $sdee->csoid = $cso->csoid;
                $sdee->update();

            }else{

                $logs["CSOID_NO_ENCONTRADO"][] = array(
                    "RUC" => $sde->sderucsubcliente,
                    "DEST" => $sde->sdecodigodestinatario,
                    "SDEID" => $sde->sdeid
                );

            }

        }

        dd($logs);

    }

    public function ConvertirDolaresBultosTotal($fecid)
    {
        $tic = tictipocambios::where('fecid', $fecid)->first();

        $cbus = cbucostosbultos::where('fecid', $fecid)->get();

        $skus = [];

        foreach ($cbus as $key => $cbu) {

            $totalCbu = $cbu->cbudirecto + $cbu->cbuindirecto;

            $cbue = cbucostosbultos::find($cbu->cbuid);
            $cbue->cbutotal = $totalCbu / $tic->tictc;
            $cbue->update();

            if(sizeof($skus) > 0){

                $encontroDuplicado = false;

                foreach ($skus as $key => $sku) {
                    if($sku == $cbu->cbusku){
                        $encontroDuplicado = true;
                    }
                }

                if($encontroDuplicado == true){
                    $cbud = cbucostosbultos::find($cbu->cbuid);
                    $cbud->delete();
                }else{
                    $skus[] = $cbu->cbusku;    
                }

            }else{
                $skus[] = $cbu->cbusku;
            }
        }

    }

    public function CrearUsuario(Request $request)
    {

        $re_numerodocumentoidentidad = $request['numerodocumentoidentidad'];
        $re_nombrecompleto           = $request['nombrecompleto'];
        $re_nombre                   = $request['nombre'];
        $re_apellidopaterno          = $request['apellidopaterno'];
        $re_apellidomaterno          = $request['apellidomaterno'];
        $re_tpuid                    = $request['tpuid'];
        $re_codigo                   = $request['codigo'];
        $re_usuario                  = $request['usuario'];
        $re_correo                   = $request['correo'];
        $re_contrasenia              = $request['contrasenia'];

        $pern = new perpersonas;
        $pern->pernumerodocumentoidentidad = $re_numerodocumentoidentidad;
        $pern->pernombrecompleto           = $re_nombrecompleto;
        $pern->pernombre                   = $re_nombre;
        $pern->perapellidopaterno          = $re_apellidopaterno;
        $pern->perapellidomaterno          = $re_apellidomaterno;
        if($pern->save()){
            $usun = new usuusuarios;
            $usun->tpuid        = $re_tpuid;
            $usun->perid        = $pern->perid;
            $usun->estid        = 1;
            $usun->usucodigo    = $re_codigo;
            $usun->usuusuario   = $re_usuario;
            $usun->usucorreo    = $re_correo;
            $usun->usucontrasenia = Hash::make($re_contrasenia);
            $usun->usutoken     =   Str::random(50);
            $usun->save();

            // usuusuarios::create([
            //     "usuid"           => 13,
            //     "tpuid"           => 2,
            //     "perid"           => 14,
            //     "estid"           => 1,
            //     "usucodigo"       => "SacValeria-09",
            //     "usuusuario"      => "vromeroe@softys.com",
            //     "usucorreo"       => "vromeroe@softys.com",
            //     "usucontrasenia"  => Hash::make('Valeria$$Romero$$82123'),
            //     "usutoken"        => "ValeriaIDMWZZwOPOR439SKSZXXZAOPALSDQ2dkka2ldrwke989230CRomero",
            // ]);
        }


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