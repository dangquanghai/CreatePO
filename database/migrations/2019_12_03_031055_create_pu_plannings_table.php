<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuPlanningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pu_plannings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_week')->nullable();
            $table->integer('at_year')->nullable();
            $table->date('order_date')->nullable();
            
            $table->integer('vendor_id')->nullable();
            $table->date('eta')->nullable();
            $table->date('end_selling_date')->nullable();
            
            $table->integer('etd')->nullable();
            $table->integer('lead_time')->nullable();
            $table->string('selling_week')->nullable();
            $table->integer('status_id')->default (1);
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
        Schema::dropIfExists('pu_plannings');
    }
}
