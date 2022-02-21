<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeripheralIndustryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peripheral_industry', function (Blueprint $table) {
            $table->foreignId('peripherals_id')
                  ->comment('外设ID')
                  ->constrained();
            $table->foreignId('industries_id')
                  ->comment('行业ID')
                  ->constrained();

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
        Schema::dropIfExists('peripheral_industry');
    }
}
