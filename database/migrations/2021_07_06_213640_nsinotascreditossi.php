<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Nsinotascreditossi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nsinotascreditossi', function (Blueprint $table) {
            $table->increments('nsiid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('tpcid');
            $table->unsignedInteger('secid');
            $table->string('nsimoneda');
            $table->string('nsiclase');
            $table->string('nsifecha');
            $table->string('nsisap');
            $table->string('nsinotacredito');
            $table->string('nsivalorneto');
            $table->string('nsivalornetodolares');

            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('tpcid')->references('tpcid')->on('tpctiposcomprobantes');
            $table->foreign('secid')->references('secid')->on('secseriescomprobantes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nsinotascreditossi');
    }
}
