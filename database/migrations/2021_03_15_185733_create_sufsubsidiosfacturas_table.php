<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSufsubsidiosfacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sufsubsidiosfacturas', function (Blueprint $table) {
            $table->increments('sufid');
            $table->unsignedInteger('sdeid');
            $table->unsignedInteger('facid');
            $table->unsignedInteger('fadid');
            $table->unsignedInteger('ncdid');
            $table->unsignedInteger('ntcid');
            $table->unsignedInteger('proid');
            $table->timestamps();

            $table->foreign('sdeid')->references('sdeid')->on('sdesubsidiosdetalles');
            $table->foreign('facid')->references('facid')->on('facfacturas');
            $table->foreign('fadid')->references('fadid')->on('fadfacturasdetalles');
            $table->foreign('ncdid')->references('ncdid')->on('ncdnotascreditosdetalles');
            $table->foreign('ntcid')->references('ntcid')->on('ntcnotascreditos');
            $table->foreign('proid')->references('proid')->on('proproductos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sufsubsidiosfacturas');
    }
}
