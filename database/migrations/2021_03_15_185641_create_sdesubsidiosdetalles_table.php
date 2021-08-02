<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSdesubsidiosdetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sdesubsidiosdetalles', function (Blueprint $table) {
            $table->increments('sdeid');
            // $table->unsignedInteger('subid');
            $table->unsignedInteger('fecid');
            $table->unsignedInteger('proid');
            $table->unsignedInteger('cliid');
            $table->string('sdecodigosolicitante');
            $table->string('sdecodigodestinatario');
            $table->string('sdesectoruno')->nullable();
            $table->string('sdesegmentoscliente')->nullable();
            $table->string('sdesubsegmentoscliente')->nullable();
            $table->string('sderucsubcliente');
            $table->string('sdesubcliente');
            $table->string('sdenombrecomercial')->nullable();
            $table->string('sdesector');
            $table->string('sdecodigounitario');
            $table->string('sdedescripcion');
            $table->string('sdepcsapfinal');
            $table->string('sdedscto');
            $table->string('sdepcsubsidiado');
            $table->string('sdemup');
            $table->string('sdepvpigv');
            $table->string('sdedsctodos');
            $table->string('sdedestrucsap');
            $table->string('sdeinicio');
            $table->string('sdebultosacordados')->nullable();
            $table->string('sdecantidadbultos')->nullable();
            $table->string('sdemontoareconocer')->nullable();
            $table->string('sdecantidadbultosreal')->nullable();
            $table->string('sdemontoareconocerreal')->nullable();
            $table->string('sdestatus')->nullable();
            $table->string('sdediferenciaahorro')->nullable();
            $table->boolean('sdesac')->default(0);
            $table->boolean('sdesacfactura')->default(0);
            $table->boolean('sdeaprobado')->default(0);
            
            $table->boolean('sdependiente')->default(0);
            $table->boolean('sdeencontrofactura')->default(0);


            // $table->string('sdecantidad');
            // $table->string('sdetotal');
            $table->timestamps();

            // $table->foreign('subid')->references('subid')->on('subsubsidios');
            $table->foreign('fecid')->references('fecid')->on('fecfechas');
            $table->foreign('proid')->references('proid')->on('proproductos');
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
        Schema::dropIfExists('sdesubsidiosdetalles');
    }
}
