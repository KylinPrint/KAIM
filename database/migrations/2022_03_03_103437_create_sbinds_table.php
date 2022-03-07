<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSbindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sbinds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('softwares_id')
                  ->comment('软件名')
                  ->constrained();
            $table->foreignId('releases_id')
                  ->comment('操作系统')
                  ->constrained();
            $table->foreignId('chips_id')
                  ->comment('芯片')
                  ->constrained();
            $table->foreignId('statuses_id')
                  ->comment('状态')
                  ->constrained();
            $table->string('softname')->comment('软件包名');
            $table->string('crossover')->nullable()->comment('crossover版本');
            $table->string('box86')->nullable()->comment('box86版本');
            $table->boolean('appstore')->nullable()->comment('是否上架软件商店');
            $table->string('filename')->comment('文件名');
            $table->string('source')->comment('安装包来源');
            $table->string('kernel_version')->nullable()->comment('内核引用版本');
            $table->string('kernel_test')->nullable()->comment('内核项测试结果');
            $table->string('apptype')->nullable()->comment('适配类型');
            $table->string('class')->comment('兼容等级');
            $table->boolean('kylineco')->comment('是否上传到生态官网');
            $table->string('comment')->nullable()->comment('备注');
            
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
        Schema::dropIfExists('sbinds');
    }
}
