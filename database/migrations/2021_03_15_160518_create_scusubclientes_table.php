<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScusubclientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scusubclientes', function (Blueprint $table) {
            $table->increments('scuid');
            $table->unsignedInteger('cliid');
            $table->string('scunombre');
            $table->string('scucodigo');
            $table->timestamps();

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
        Schema::dropIfExists('scusubclientes');
    }
}
