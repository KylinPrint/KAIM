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
            $table->string('name')->comment('外设型号')->unique();

            $table->foreignId('manufactors_id')
                  ->nullable()
                  ->comment('外设厂商')
                  ->constrained();
            $table->foreignId('brands_id')
                  ->comment('外设品牌')
                  ->constrained();
            $table->foreignId('types_id')
                  ->comment('外设类型')
                  ->constrained();
            $table->date('release_date')->nullable()->comment('发布日期');
            $table->date('eosl_date')->nullable()->comment('服务终止日期');
            $table->string('comment')->nullable()->comment('外设描述');

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
