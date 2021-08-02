<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFadfacturasdetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fadfacturasdetalles', function (Blueprint $table) {
            $table->increments('fadid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('facid');
            $table->unsignedInteger('proid');
            $table->unsignedInteger('cliid');
            $table->string('fadcantidad');
            $table->string('fadpreciounitario');
            $table->string('fadsubtotal');
            $table->string('fadimpuesto');
            $table->string('fadtotal');
            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('facid')->references('facid')->on('facfacturas');
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
        Schema::dropIfExists('fadfacturasdetalles');
    }
}
