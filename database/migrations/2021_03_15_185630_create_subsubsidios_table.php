<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubsubsidiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subsubsidios', function (Blueprint $table) {
            $table->increments('subid');
            $table->unsignedInteger('cliid');
            $table->unsignedInteger('fecid');
            $table->string('subtotal');
            $table->timestamps();

            $table->foreign('cliid')->references('cliid')->on('cliclientes');
            $table->foreign('fecid')->references('fecid')->on('fecfechas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subsubsidios');
    }
}
