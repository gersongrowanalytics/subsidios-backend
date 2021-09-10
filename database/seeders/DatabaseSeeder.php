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


        // estestados::create([
        //     "estnombre" => "ACTIVADO",
        //     "estdescripcion" => "Para todos los datos que esten disponibles",
        // ]);

        // estestados::create([
        //     "estnombre" => "DESACTIVADO",
        //     "estdescripcion" => "Para todos los datos que NO esten disponibles",
        // ]);

        // perpersonas::create([
        //     "pernumerodocumentoidentidad" => "73819654",
        //     "pernombrecompleto"           => "Gerson Vilca Alvarez",
        //     "pernombre"                   => "Gerson",
        //     "perapellidopaterno"          => "Vilca",
        //     "perapellidomaterno"          => "Alvarez",
        // ]);

        // perpersonas::create([
        //     "pernumerodocumentoidentidad" => "0000000",
        //     "pernombrecompleto"           => "Maria Yauri",
        //     "pernombre"                   => "Maria",
        //     "perapellidopaterno"          => "Yauri",
        //     "perapellidomaterno"          => "",
        // ]);

        // perpersonas::create([
        //     "pernumerodocumentoidentidad" => "0000000",
        //     "pernombrecompleto"           => "Soporte Grow",
        //     "pernombre"                   => "Soporte",
        //     "perapellidopaterno"          => "Grow",
        //     "perapellidomaterno"          => "",
        // ]);

        // tputiposusuarios::create([
        //     "tpunombre"      => "Administrador",
        //     "tpuprivilegio"  => "todo",
        // ]);

        // usuusuarios::create([
        //     "tpuid"           => 1,
        //     "perid"           => 1,
        //     "estid"           => 1,
        //     "usucodigo"       => "GROWDEV-01",
        //     "usuusuario"      => "Administrador",
        //     "usucorreo"       => "gerson.vilca@grow-analytics.com",
        //     "usucontrasenia"  => Hash::make('gerson$$'),
        //     "usutoken"        => "TOKENESPECIFICOUNIFODEVGERSONGROW1845475#LD72",
        // ]);

        // usuusuarios::create([
        //     "tpuid"           => 1,
        //     "perid"           => 2,
        //     "estid"           => 1,
        //     "usucodigo"       => "01",
        //     "usuusuario"      => "maria.yauri@softys.com",
        //     "usucorreo"       => "maria.yauri@softys.com",
        //     "usucontrasenia"  => Hash::make('Maria$$Yauri$$39232'),
        //     "usutoken"        => "ToKEnMariaYa2339281dkshqqqoakw3i4ksl3lrkfFAOQ23",
        // ]);

        // usuusuarios::create([
        //     "tpuid"           => 1,
        //     "perid"           => 3,
        //     "estid"           => 1,
        //     "usucodigo"       => "GROWSOPORTE-01",
        //     "usuusuario"      => "soporte@grow-analytics.com.pe",
        //     "usucorreo"       => "soporte@grow-analytics.com.pe",
        //     "usucontrasenia"  => Hash::make('Soporte$$Grow$$029213'),
        //     "usutoken"        => "StoKEsOPOR43920TE023lddddowke20349to123OUot249",
        // ]);

        // // 

        // tcatiposcargasarchivos::create([
        //     "usuid"                   => 1,
        //     "tcanombre"               => "Subsidios No Aprobados",
        //     "tcaresponsable"          => "",
        //     "tcabasedatos"            => "Subsidios No Aprobados",
        //     "tcaarea"                 => "Revenue",
        //     "tcafechacargaprogramada" => "26 de Junio del 2021",
        // ]);

        // tcatiposcargasarchivos::create([
        //     "usuid"                   => 1,
        //     "tcanombre"               => "Facturas SO",
        //     "tcaresponsable"          => "",
        //     "tcabasedatos"            => "Facturas SO",
        //     "tcaarea"                 => "Revenue",
        //     "tcafechacargaprogramada" => "26 de Junio del 2021",
        // ]);

        // tcatiposcargasarchivos::create([
        //     "usuid"                   => 1,
        //     "tcanombre"               => "Subsidios Pre-Aprobados",
        //     "tcaresponsable"          => "",
        //     "tcabasedatos"            => "Subsidios Pre-Aprobados",
        //     "tcaarea"                 => "SAC",
        //     "tcafechacargaprogramada" => "26 de Junio del 2021",
        // ]);

        // tcatiposcargasarchivos::create([
        //     "usuid"                   => 1,
        //     "tcanombre"               => "Consolidación de Información SO",
        //     "tcaresponsable"          => "",
        //     "tcabasedatos"            => "Consolidación de Información SO+",
        //     "tcaarea"                 => "GROW",
        //     "tcafechacargaprogramada" => "26 de Junio del 2021",
        // ]);

        // tpctiposcomprobantes::create([
        //     "tpccodigo" => "01",
        //     "tpcnombre" => "Factura",
        // ]);

        // tpctiposcomprobantes::create([
        //     "tpccodigo" => "03",
        //     "tpcnombre" => "Boleta de Venta",
        // ]);

        // tpctiposcomprobantes::create([
        //     "tpccodigo" => "07",
        //     "tpcnombre" => "Nota de crédito",
        // ]);


        // tputiposusuarios::create([
        //     "tpunombre"      => "Cliente",
        //     "tpuprivilegio"  => "",
        // ]);

        // perpersonas::create([
        //     "pernumerodocumentoidentidad" => "0000000",
        //     "pernombrecompleto"           => "Diego Morales",
        //     "pernombre"                   => "Diego",
        //     "perapellidopaterno"          => "Morales",
        //     "perapellidomaterno"          => "",
        // ]);

        // perpersonas::create([
        //     "pernumerodocumentoidentidad" => "0000000",
        //     "pernombrecompleto"           => "Anthony Quiroz",
        //     "pernombre"                   => "Anthony",
        //     "perapellidopaterno"          => "Quiroz",
        //     "perapellidomaterno"          => "",
        // ]);

        // usuusuarios::create([
        //     "tpuid"           => 2,
        //     "perid"           => 2,
        //     "estid"           => 1,
        //     "usucodigo"       => "SoftSacDMorales-01",
        //     "usuusuario"      => "dmorales@softys.com",
        //     "usucorreo"       => "dmorales@softys.com",
        //     "usucontrasenia"  => Hash::make('Diego$$Morales$$29317'),
        //     "usutoken"        => "SDiegosOPOR4392DiMo23ldddd231owkMoral123OUot249",
        // ]);

        // usuusuarios::create([
        //     "tpuid"           => 2,
        //     "perid"           => 3,
        //     "estid"           => 1,
        //     "usucodigo"       => "SoftSacAQuiroz-01",
        //     "usuusuario"      => "aquiroz@softys.com",
        //     "usucorreo"       => "aquiroz@softys.com",
        //     "usucontrasenia"  => Hash::make('Anthony$$Quiroz$$293113'),
        //     "usutoken"        => "AnthonysOPOR4392asdopwddddowke20349to123OQuiroz",
        // ]);


        // perpersonas::create([
        //     "pernumerodocumentoidentidad" => "0000000",
        //     "pernombrecompleto"           => "Alonso Roel",
        //     "pernombre"                   => "Alonso",
        //     "perapellidopaterno"          => "Roel",
        //     "perapellidomaterno"          => "",
        // ]);

        // usuusuarios::create([
        //     "tpuid"           => 2,
        //     "perid"           => 4,
        //     "estid"           => 1,
        //     "usucodigo"       => "SoftSacAroel-01",
        //     "usuusuario"      => "aroel@softys.com",
        //     "usucorreo"       => "aroel@softys.com",
        //     "usucontrasenia"  => Hash::make('Alonso$$Roel$$982831'),
        //     "usutoken"        => "AlonsowOPOR439223ldsawpwddmxdowke989230to123Roel",
        // ]);

        // perpersonas::create([
        //     "pernumerodocumentoidentidad" => "0000000",
        //     "pernombrecompleto"           => "Eduardo Soto Mayor",
        //     "pernombre"                   => "Eduardo",
        //     "perapellidopaterno"          => "Soto",
        //     "perapellidomaterno"          => "Mayor",
        // ]);

        // usuusuarios::create([
        //     "tpuid"           => 2,
        //     "perid"           => 4,
        //     "estid"           => 1,
        //     "usucodigo"       => "SoftSacAroel-01",
        //     "usuusuario"      => "esotomayor@softys.com",
        //     "usucorreo"       => "esotomayor@softys.com",
        //     "usucontrasenia"  => Hash::make('Eduardo$$Soto$$23414554'),
        //     "usutoken"        => "EduardoowOPOR4392Maysawq222dkka2ldowke989230to123Stoo",
        // ]);


        perpersonas::create([
            "pernumerodocumentoidentidad" => "0000000",
            "pernombrecompleto"           => "Miguel Caballero",
            "pernombre"                   => "Miguel",
            "perapellidopaterno"          => "Caballero",
            "perapellidomaterno"          => "",
        ]);

        usuusuarios::create([
            "tpuid"           => 1,
            "perid"           => 5,
            "estid"           => 1,
            "usucodigo"       => "SoftSacAroel-01",
            "usuusuario"      => "miguel.caballero@grow-analytics.com.pe",
            "usucorreo"       => "miguel.caballero@grow-analytics.com.pe",
            "usucontrasenia"  => Hash::make('Miguel$$Caballero$$283711'),
            "usutoken"        => "MiguelwOPOR439asd11aaawq222dkka2ldrwke989230Caballeroo",
        ]);
    }
}
