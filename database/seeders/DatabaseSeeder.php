<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\models\estestados;
use App\models\perpersonas;
use App\models\tputiposusuarios;
use App\models\usuusuarios;
use App\models\tcatiposcargasarchivos;
use App\models\carcargasarchivos;
use App\models\tpctiposcomprobantes;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('estestadosSeeder');
        // $this->call('perpersonasSeeder');
        // $this->call('tputiposusuariosSeeder');
        // $this->call('usuusuariosSeeder');


        estestados::create([
            "estnombre" => "ACTIVADO",
            "estdescripcion" => "Para todos los datos que esten disponibles",
        ]);

        estestados::create([
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

        // 

        tcatiposcargasarchivos::create([
            "usuid"                   => 1,
            "tcanombre"               => "Subsidios No Aprobados",
            "tcaresponsable"          => "",
            "tcabasedatos"            => "Subsidios No Aprobados",
            "tcaarea"                 => "Revenue",
            "tcafechacargaprogramada" => "26 de Junio del 2021",
        ]);

        tcatiposcargasarchivos::create([
            "usuid"                   => 1,
            "tcanombre"               => "Facturas SO",
            "tcaresponsable"          => "",
            "tcabasedatos"            => "Facturas SO",
            "tcaarea"                 => "Revenue",
            "tcafechacargaprogramada" => "26 de Junio del 2021",
        ]);

        tcatiposcargasarchivos::create([
            "usuid"                   => 1,
            "tcanombre"               => "Subsidios Pre-Aprobados",
            "tcaresponsable"          => "",
            "tcabasedatos"            => "Subsidios Pre-Aprobados",
            "tcaarea"                 => "SAC",
            "tcafechacargaprogramada" => "26 de Junio del 2021",
        ]);

        tcatiposcargasarchivos::create([
            "usuid"                   => 1,
            "tcanombre"               => "Consolidación de Información SO",
            "tcaresponsable"          => "",
            "tcabasedatos"            => "Consolidación de Información SO+",
            "tcaarea"                 => "GROW",
            "tcafechacargaprogramada" => "26 de Junio del 2021",
        ]);

        tpctiposcomprobantes::create([
            "tpccodigo" => "01",
            "tpcnombre" => "Factura",
        ]);

        tpctiposcomprobantes::create([
            "tpccodigo" => "03",
            "tpcnombre" => "Boleta de Venta",
        ]);

        tpctiposcomprobantes::create([
            "tpccodigo" => "07",
            "tpcnombre" => "Nota de crédito",
        ]);
    }
}
