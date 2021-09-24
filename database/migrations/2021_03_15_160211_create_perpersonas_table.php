<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerpersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perpersonas', function (Blueprint $table) {
            $table->increments('perid');
            $table->string('pernumerodocumentoidentidad')->nullable();
            $table->string('pernombrecompleto');
            $table->string('pernombre')->nullable();
            $table->string('perapellidopaterno')->nullable();
            $table->string('perapellidomaterno')->nullable();
            $table->string('percumpleanios')->nullable();
            $table->string('pernumero')->nullable();
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
        Schema::dropIfExists('perpersonas');
    }
}
