<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUscusuariosclientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ussusuariosclientes', function (Blueprint $table) {
            $table->increments('ussid');
            $table->unsignedInteger('usuid');
            $table->unsignedInteger('cliid');
            $table->timestamps();

            $table->foreign('usuid')->references('usuid')->on('usuusuarios');
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
        Schema::dropIfExists('ussusuariosclientes');
    }
}
