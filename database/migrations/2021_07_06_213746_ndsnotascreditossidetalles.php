<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Ndsnotascreditossidetalles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ndsnotascreditossidetalles', function (Blueprint $table) {
            $table->increments('ndsid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('nsiid');
            $table->unsignedInteger('fsiid');
            $table->unsignedInteger('proid');
            $table->unsignedInteger('cliid');
            $table->string('ndsmaterial');
            $table->string('ndsclase');
            $table->string('ndsnotacredito');
            $table->string('ndsvalorneto');
            $table->string('ndsvalornetodolares');
            $table->string('ndspedido');
            $table->string('ndspedidooriginal');
            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('nsiid')->references('nsiid')->on('nsinotascreditossi');
            $table->foreign('fsiid')->references('fsiid')->on('fsifacturassi');
            $table->foreign('proid')->references('proid')->on('proproductos');
            $table->foreign('cliid')->references('cliid')->on('cliclientes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ndsnotascreditossidetalles');
    }
}
