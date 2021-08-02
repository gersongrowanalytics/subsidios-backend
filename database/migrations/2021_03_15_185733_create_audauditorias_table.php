<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAudauditoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audauditorias', function (Blueprint $table) {
            $table->increments('audid');
            $table->unsignedInteger('usuid')->nullable();
            $table->string('audip')->nullable();
            $table->text('audjsonentrada');
            $table->text('audjsonsalida');
            $table->text('auddescripcion');
            $table->string('audaccion');
            $table->string('audruta');
            $table->text('audlog')->nullable();
            $table->string('audpk')->nullable();
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
        Schema::dropIfExists('audauditorias');
    }
}
