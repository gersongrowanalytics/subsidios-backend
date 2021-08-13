<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Areareasestados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areareasestados', function (Blueprint $table) {
            $table->increments('areid');
            $table->unsignedInteger('tprid');
            $table->string('areicono');
            $table->string('arenombre');
            $table->string('areporcentaje');

            $table->timestamps();

            $table->foreign('tprid')->references('tprid')->on('tprtipospromociones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areareasestados');
    }
}
