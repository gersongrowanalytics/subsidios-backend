<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNtcnotascreditosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ntcnotascreditos', function (Blueprint $table) {
            $table->increments('ntcid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('facid')->nullable();
            $table->unsignedInteger('cliid');
            $table->unsignedInteger('tpcid');
            $table->unsignedInteger('secid');
            $table->string('ntccodigocompleto')->unique();
            $table->string('ntcfacturaasignada');
            $table->string('ntcserie');
            $table->string('ntccorrelativo');
            $table->string('ntccodigo');
            $table->string('ntcsap');
            $table->string('ntcsubtotal');
            $table->string('ntcimpuesto');
            $table->string('ntctotal');
            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('facid')->references('facid')->on('facfacturas');
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
        Schema::dropIfExists('ntcnotascreditos');
    }
}
