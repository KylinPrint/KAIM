<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufactor_id')
                  ->comment('厂商名称')
                  ->constrained();
            $table->string('name')->comment('型号');
            $table->string('type_id')->comment('分类');
            $table->string('source')->comment('引入来源');
            $table->string('details')->nullable()->comment('产品描述');
            $table->foreignId('release_id')
                  ->comment('操作系统版本号')
                  ->constrained();
            $table->string('os_subversion')->nullable()->comment('操作系统小版本号');
            $table->foreignId('chip_id')
                  ->comment('芯片ID')
                  ->constrained();
            $table->foreignId('status_id')
                  ->comment('当前适配状态')
                  ->constrained();
            $table->string('user_name')->comment('当前适配状态责任人');
            $table->string('class')->nullable()->comment('兼容等级');
            $table->string('test_type')->nullable()->comment('测试方式');

            $table->boolean('kylineco')->comment('是否上传生态网站');
            $table->boolean('iscert')->comment('是否互认证');
            $table->string('patch')->comment('补丁包连接');
            $table->date('start_time')->nullable()->comment('适配开始时间');
            $table->date('complete_time')->nullable()->comment('适配完成时间');
            $table->string('motherboard')->nullable()->comment('主板品牌及型号');
            $table->string('gpu')->nullable()->comment('gpu品牌及型号');
            $table->string('graphic_card')->nullable()->comment('显卡品牌及型号');
            $table->string('ai_card')->nullable()->comment('Ai加速卡品牌及型号');
            $table->string('network')->nullable()->comment('网卡品牌及型号');
            $table->string('memory')->nullable()->comment('内存品牌及型号');
            $table->string('raid')->nullable()->comment('RAID卡品牌及型号');
            $table->string('hba')->nullable()->comment('HBA卡品牌及型号');
            $table->string('hard_disk')->nullable()->comment('硬盘品牌及型号');
            $table->string('firmware')->nullable()->comment('固件品牌及型号');
            $table->string('sound_card')->nullable()->comment('声卡品牌及型号');
            $table->string('parallel')->nullable()->comment('并口卡品牌及型号');
            $table->string('serial')->nullable()->comment('串口卡品牌及型号');
            $table->string('isolation_card')->nullable()->comment('隔离卡品牌及型号');
            $table->string('other_card')->nullable()->comment('其它板卡品牌及型号');
            $table->string('comment')->nullable()->comment('备注');
            $table->timestamps();

            $table->unique(['manufactor_id', 'name', 'release_id', 'chip_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oems');
    }
}
