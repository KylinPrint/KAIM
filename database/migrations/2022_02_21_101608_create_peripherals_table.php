<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeripheralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peripherals', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('外设名称');

            $table->foreignId('brands_id')
                  ->comment('外设品牌')
                  ->constrained();
            $table->foreignId('types_id')
                  ->comment('外设分类')
                  ->constrained();
            $table->date('release_date')->nullable()->comment('发布日期');
            $table->date('eosl_date')->nullable()->comment('服务终止日期');

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
        Schema::dropIfExists('peripherals');
    }
}
