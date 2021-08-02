<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\usuusuarios;
use Illuminate\Support\Facades\Hash;

class usuusuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
    }
}
