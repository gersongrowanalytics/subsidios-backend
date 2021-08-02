<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProproductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proproductos', function (Blueprint $table) {
            $table->increments('proid');
            $table->unsignedInteger('cosid')->nullable();
            $table->unsignedInteger('conid')->nullable();
            $table->unsignedInteger('catid')->nullable();
            $table->unsignedInteger('marid')->nullable();
            $table->string('pronombre')->nullable();
            $table->string('prosku')->nullable();
            $table->string('prosegmentacion')->nullable();
            $table->string('propresentacion')->nullable();
            $table->string('proconteo')->nullable();
            $table->string('proformato')->nullable();
            $table->string('protalla')->nullable();
            $table->string('propeso')->nullable();
            $table->string('promecanica')->nullable();
            $table->string('profactorconversionbultos')->nullable();
            $table->string('profactorconversioncajas')->nullable();
            $table->string('profactorconversionpaquetes')->nullable();
            $table->string('profactorconversionunidadminimaindivisible')->nullable();
            $table->string('profactorconversiontoneladas')->nullable();
            $table->string('profactorconversionmilesunidades')->nullable();
            $table->timestamps();

            $table->foreign('cosid')->references('cosid')->on('coscodigossectores');
            $table->foreign('conid')->references('conid')->on('concodigosnegocios');
            $table->foreign('catid')->references('catid')->on('catcategorias');
            $table->foreign('marid')->references('marid')->on('marmarcas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proproductos');
    }
}
