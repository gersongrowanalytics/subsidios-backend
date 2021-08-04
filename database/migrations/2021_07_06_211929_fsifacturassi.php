<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Fsifacturassi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fsifacturassi', function (Blueprint $table) {
            $table->increments('fsiid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('cliid');
            $table->unsignedInteger('tpcid');
            $table->unsignedInteger('secid');
            $table->string('fsisolicitante');
            $table->string('fsidestinatario');
            $table->string('fsimoneda');
            $table->string('fsiclase');
            $table->string('fsifecha');
            $table->string('fsisap');
            $table->string('fsifactura');
            $table->string('fsivalorneto');
            $table->string('fsivalornetodolares');
            $table->string('fsipedido');
            $table->string('fsipedidooriginal');
            $table->boolean('fsisunataprobado')->default(1);

            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('cliid')->references('cliid')->on('cliclientes');
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
        Schema::dropIfExists('fsifacturassi');
    }
}
