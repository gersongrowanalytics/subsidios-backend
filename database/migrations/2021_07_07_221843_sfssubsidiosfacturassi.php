<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Sfssubsidiosfacturassi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfssubsidiosfacturassi', function (Blueprint $table) {
            $table->increments('sfsid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('sdeid');
            $table->unsignedInteger('fsiid')->nullable();
            $table->unsignedInteger('fdsid');
            $table->unsignedInteger('nsiid')->nullable();
            $table->unsignedInteger('ndsid')->nullable();
            $table->string('sfsvalorizado');
            $table->string('sfssaldoanterior');
            $table->string('sfssaldonuevo');

            $table->string('sfsobjetivo');
            $table->string('sfsdiferenciaobjetivo');

            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('sdeid')->references('sdeid')->on('sdesubsidiosdetalles');
            $table->foreign('fsiid')->references('fsiid')->on('fsifacturassi');
            $table->foreign('fdsid')->references('fdsid')->on('fdsfacturassidetalles');
            $table->foreign('nsiid')->references('nsiid')->on('nsinotascreditossi');
            $table->foreign('ndsid')->references('ndsid')->on('ndsnotascreditossidetalles');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sfssubsidiosfacturassi');
    }
}
