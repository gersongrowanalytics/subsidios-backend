<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNcdnotascreditosdetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ncdnotascreditosdetalles', function (Blueprint $table) {
            $table->increments('ncdid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('ntcid');
            $table->unsignedInteger('facid');
            $table->unsignedInteger('cliid');
            $table->unsignedInteger('proid');
            $table->unsignedInteger('sdeid')->nullable();
            $table->string('ncdcantidad');
            $table->string('ncdtotal');
            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('ntcid')->references('ntcid')->on('ntcnotascreditos');
            $table->foreign('facid')->references('facid')->on('facfacturas');
            $table->foreign('cliid')->references('cliid')->on('cliclientes');
            $table->foreign('proid')->references('proid')->on('proproductos');
            $table->foreign('sdeid')->references('sdeid')->on('sdesubsidiosdetalles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ncdnotascreditosdetalles');
    }
}
