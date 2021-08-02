<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacfacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facfacturas', function (Blueprint $table) {
            $table->increments('facid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('cliid');
            $table->unsignedInteger('tpcid');
            $table->unsignedInteger('secid');
            $table->string('faccodigocompleto')->unique();
            $table->string('facserie');
            $table->string('faccorrelativo');
            $table->string('faccodigo');
            $table->string('facsap');
            $table->string('facsubtotal');
            $table->string('facimpuesto');
            $table->string('factotal');
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
        Schema::dropIfExists('facfacturas');
    }
}
