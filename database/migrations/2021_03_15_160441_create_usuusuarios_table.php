<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuusuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuusuarios', function (Blueprint $table) {
            $table->increments('usuid');
            $table->unsignedInteger('tpuid');
            $table->unsignedInteger('perid');
            $table->unsignedInteger('estid')->default(1);
            $table->string('usucodigo')->nullable();
            $table->string('usuusuario');
            $table->string('usucorreo')->nullable();
            $table->string('usucontrasenia');
            $table->string('usutoken');
            $table->string('usuimagen')->nullable();
            $table->timestamps();

            $table->foreign('tpuid')->references('tpuid')->on('tputiposusuarios');
            $table->foreign('perid')->references('perid')->on('perpersonas');
            $table->foreign('estid')->references('estid')->on('estestados');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuusuarios');
    }
}
