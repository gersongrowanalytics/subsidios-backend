<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Secseriescomprobantes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secseriescomprobantes', function (Blueprint $table) {
            $table->increments('secid');
            $table->unsignedInteger('tpcid');
            $table->string('secserie');
            $table->string('secdescripcion');
            $table->timestamps();

            $table->foreign('tpcid')->references('tpcid')->on('tpctiposcomprobantes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('secseriescomprobantes');
    }
}
