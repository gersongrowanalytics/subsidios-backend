<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\estestados;

class EsstestadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        estestados::create([
            "estid" => 1,
            "estnombre" => "ACTIVADO",
            "estdescripcion" => "Para todos los datos que esten disponibles",
        ]);

        estestados::create([
            "estid" => 2,
            "estnombre" => "DESACTIVADO",
            "estdescripcion" => "Para todos los datos que NO esten disponibles",
        ]);
    }
}
