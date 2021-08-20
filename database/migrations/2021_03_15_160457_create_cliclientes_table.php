<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCliclientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliclientes', function (Blueprint $table) {
            $table->increments('cliid');
            $table->unsignedInteger('zonid');
            $table->string('clinombre');
            $table->string('clicodigo')->nullable();
            $table->string('clicodigoshipto')->nullable();
            $table->string('clishipto')->nullable();
            $table->string('clihml')->nullable();
            $table->string('clisuchml')->nullable();
            $table->string('clidepartamento')->nullable();
            $table->string('cligrupohml')->nullable();
            $table->string('clitv')->nullable();
            $table->string('clizona')->nullable();
            $table->string('cliregion')->nullable();
            $table->string('clicanal')->nullable();
            $table->string('clitipoatencion')->nullable();
            $table->string('clicanalatencion')->nullable();
            $table->string('clisegmentoclientefinal')->nullable();
            $table->string('clisubsegmento')->nullable();
            $table->string('clisegmentoregional')->nullable();
            $table->string('cligerenteregional')->nullable();
            $table->string('cligerentezona')->nullable();
            $table->string('cliejecutivo')->nullable();
            $table->string('cliidentificadoraplicativo')->nullable();
            $table->string('cliclientesac')->default(0);
            $table->timestamps();

            $table->foreign('zonid')->references('zonid')->on('zonzonas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliclientes');
    }
}
