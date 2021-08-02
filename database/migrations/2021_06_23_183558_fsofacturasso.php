<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Fsofacturasso extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fsofacturasso', function (Blueprint $table) {
            $table->increments('fsoid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('cliid');
            $table->unsignedInteger('proid');
            $table->string('fsoruc')->nullable();
            $table->string('fsocantidadbulto');
            $table->string('fsoventasinigv');
            $table->timestamps();

            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('cliid')->references('cliid')->on('cliclientes');
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
        Schema::dropIfExists('fsofacturasso');
    }
}
