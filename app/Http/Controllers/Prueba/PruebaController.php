<?php

namespace App\Http\Controllers\Prueba;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\estestados;
use App\Models\perpersonas;
use App\Models\tputiposusuarios;
use App\Models\usuusuarios;
use App\Models\tpctiposcomprobantes;

class PruebaController extends Controller
{
    public function EjecutarSeeds()
    {
        estestados::create([
            // "estid" => 1,
            "estnombre" => "ACTIVADO",
            "estdescripcion" => "Para todos los datos que esten disponibles",
        ]);

        estestados::create([
            // "estid" => 2,
            "estnombre" => "DESACTIVADO",
            "estdescripcion" => "Para todos los datos que NO esten disponibles",
        ]);

        perpersonas::create([
            "pernumerodocumentoidentidad" => "73819654",
            "pernombrecompleto"           => "GERSON VILCA ALVAREZ",
            "pernombre"                   => "Gerson",
            "perapellidopaterno"          => "Vilca",
            "perapellidomaterno"          => "Alvarez",
        ]);

        tputiposusuarios::create([
            "tpunombre"      => "Administrador",
            "tpuprivilegio"  => "todo",
        ]);

        usuusuarios::create([
            "tpuid"           => 1,
            "perid"           => 1,
            "estid"           => 1,
            "usucodigo"       => "GROWDEV-01",
            "usuusuario"      => "Administrador",
            "usucorreo"       => "gerson.vilca@grow-analytics.com",
            "usucontrasenia"  => Hash::make('gerson$$'),
            "usutoken"        => "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72",
        ]);
        

        // Tipos de comprobantes (tpc)
        tpctiposcomprobantes::create([
            "tpccodigo" => "01",
            "tpcnombre" => "FACTURA",
        ]);

        tpctiposcomprobantes::create([
            "tpccodigo" => "03",
            "tpcnombre" => "BOLETA",
        ]);

        tpctiposcomprobantes::create([
            "tpccodigo" => "07",
            "tpcnombre" => "NOTA DE CREDITO",
        ]);
    }
}
