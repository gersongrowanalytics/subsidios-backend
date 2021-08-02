<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\perpersonas;

class perpersonasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        perpersonas::create([
            "pernumerodocumentoidentidad" => "73819654",
            "pernombrecompleto"           => "GERSON VILCA ALVAREZ",
            "pernombre"                   => "Gerson",
            "perapellidopaterno"          => "Vilca",
            "perapellidomaterno"          => "Alvarez",
        ]);
    }
}
