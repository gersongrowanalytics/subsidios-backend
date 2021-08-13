<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Espestadospendientes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('espestadospendientes', function (Blueprint $table) {
            $table->increments('espid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('perid');
            $table->unsignedInteger('areid');
            $table->date('espfechaprogramado')->nullable();
            $table->date('espchacargareal')->nullable();
            $table->date('espfechactualizacion')->nullable();

            $table->string('espbasedato');
            $table->string('espresponsable')->nullable();
            $table->string('espdiaretraso')->nullable();

            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('perid')->references('perid')->on('perpersonas');
            $table->foreign('areid')->references('areid')->on('areareasestados');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('espestadospendientes');
    }
}
