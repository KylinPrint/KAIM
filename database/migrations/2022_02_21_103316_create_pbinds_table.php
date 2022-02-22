<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePbindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pbinds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peripherals_id')
                  ->comment('外设ID')
                  ->constrained();
            $table->foreignId('releases_id')
                  ->comment('操作系统版本ID')
                  ->constrained();
            $table->foreignId('chips_id')
                  ->comment('芯片ID')
                  ->constrained();
            $table->foreignId('solutions_id')
                  ->comment('解决方案ID')
                  ->constrained();
            
            $table->foreignId('statuses_id')
                  ->comment('适配状态ID')
                  ->constrained();
            $table->string('class')->nullable()->comment('兼容等级');
            $table->string('comment')->nullable()->comment('备注');

            $table->timestamps();

            $table->unique(['peripherals_id', 'releases_id', 'chips_id', 'solutions_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pbinds');
    }
}
