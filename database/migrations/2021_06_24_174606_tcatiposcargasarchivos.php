<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Tcatiposcargasarchivos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tcatiposcargasarchivos', function (Blueprint $table) {
            $table->increments('tcaid');
            $table->unsignedInteger('usuid');
            $table->string('tcanombre');
            $table->string('tcaresponsable');
            $table->string('tcabasedatos');
            $table->string('tcaarea');
            $table->string('tcafechacargaprogramada');

            $table->timestamps();

            $table->foreign('usuid')->references('usuid')->on('usuusuarios');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tcatiposcargasarchivos');
    }
}
