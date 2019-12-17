<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalLoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sal_lois', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku');
            $table->integer('avcwh_level')->nullable();
            $table->integer('fba_level')->nullable();
            $table->integer('y4a_level')->nullable();
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
        Schema::dropIfExists('sal_lois');
    }
}
