<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('values', function (Blueprint $table) {
            $table->unsignedBigInteger('peripherals_id')->comment('外设ID');
            // 外设添加参数的脑瘫代码的后遗症
            // $table->foreignId('peripherals_id')
            //       ->comment('外设ID')
            //       ->constrained();
            $table->foreignId('specifications_id')
                  ->comment('参数ID')
                  ->constrained();
            
            $table->string('value')->comment('值');

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
        Schema::dropIfExists('values');
    }
}
