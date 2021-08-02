<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Carcargasarchivos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carcargasarchivos', function (Blueprint $table) {
            $table->increments('carid');
            $table->unsignedInteger('tcaid');
            $table->unsignedInteger('usuid');
            $table->string('cardiasatraso');

            $table->timestamps();

            $table->foreign('usuid')->references('usuid')->on('usuusuarios');
            $table->foreign('tcaid')->references('tcaid')->on('tcatiposcargasarchivos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carcargasarchivos');
    }
}
