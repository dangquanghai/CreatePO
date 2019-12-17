<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuContainerTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pu_container_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->float('max_cubic_m3');
            $table->float('max_weight_kg');
            $table->integer('ocean_fee')->nullable();
            $table->integer('broker_fee')->nullable();
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
        Schema::dropIfExists('pu_container_types');
    }
}
