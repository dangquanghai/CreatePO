<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuPoEstimatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pu_po_estimates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('the_week');
            $table->integer('the_year');
            $table->integer('vendor_id');
            $table->float('the_cbm_m3')->nullable();
            $table->float('the_weight_kg')->nullable();
            $table->float('per_cbm_m3')->nullable();
            $table->float('per_weight_kg')->nullable();
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
        Schema::dropIfExists('pu_po_estimates');
    }
}
