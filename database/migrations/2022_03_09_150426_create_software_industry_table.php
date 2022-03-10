<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftwareIndustryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('software_industry', function (Blueprint $table) {
            $table->foreignId('softwares_id')
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
        Schema::dropIfExists('software_industry');
    }
}
