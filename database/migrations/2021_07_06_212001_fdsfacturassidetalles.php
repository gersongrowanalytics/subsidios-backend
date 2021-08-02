<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Fdsfacturassidetalles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fdsfacturassidetalles', function (Blueprint $table) {
            $table->increments('fdsid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('fsiid');
            $table->unsignedInteger('proid');
            $table->unsignedInteger('cliid');
            $table->string('fdsmaterial');
            $table->string('fdsmoneda');
            $table->string('fdsvalorneto');
            $table->string('fdsvalornetodolares');
            $table->string('fdspedido');
            $table->string('fdspedidooriginal');

            $table->string('fdssaldo');
            $table->string('fdsreconocer');
            $table->string('fdstreintaporciento');
            $table->string('fdsnotacredito')->default(0);
            $table->boolean('fdsobservacion')->default(0);

            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
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
        Schema::dropIfExists('fdsfacturassidetalles');
    }
}
