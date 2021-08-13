<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFecfechasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fecfechas', function (Blueprint $table) {
            $table->increments('fecid');
            $table->date('fecfecha');
            $table->string('fecmesabreviacion');
            $table->string('fecdianumero', 2);
            $table->string('fecmesnumero', 2)->nullable();
            $table->string('fecanionumero', 4);
            $table->string('fecdiatexto');
            $table->string('fecmestexto');
            $table->string('fecaniotexto');
            $table->boolean('fecmesabierto')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fecfechas');
    }
}
