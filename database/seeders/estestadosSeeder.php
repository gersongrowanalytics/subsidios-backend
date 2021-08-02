<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\estestados;

class estestadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        estestados::create([
            "estnombre" => "ACTIVADO",
            "estdescripcion" => "Para todos los datos que esten disponibles",
        ]);

        estestados::create([
            "estnombre" => "DESACTIVADO",
            "estdescripcion" => "Para todos los datos que NO esten disponibles",
        ]);
    }
}
