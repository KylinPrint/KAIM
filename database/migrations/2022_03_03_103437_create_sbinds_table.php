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
                  ->comment('软件ID')
                  ->constrained();
            $table->foreignId('releases_id')
                  ->comment('操作系统版本ID')
                  ->constrained();
            $table->string('os_subversion')->nullable()->comment('操作系统小版本号');
            $table->foreignId('chips_id')
                  ->comment('芯片ID')
                  ->constrained();
            $table->string('adapt_source')
                  ->comment('引入来源[厂商主动申请,BD主动拓展,行业营销中心引入,区域营销中心引入,最终客户反馈,产品经理引入,厂商合作事业本部引入,渠道部引入,相关机构反馈,其他方式引入]');
            $table->boolean('adapted_before')->nullable()->comment('是否适配过国产CPU');
            $table->foreignId('statuses_id')
                  ->comment('当前适配状态')
                  ->constrained();
            $table->unsignedBigInteger('admin_users_id')->nullable()->comment('当前适配状态责任人');
            $table->string('solution')->nullable()->comment('适配方案');
            $table->string('class')->nullable()->comment('兼容等级[READY,CERTIFICATION,VALIDATION,PM]');
            $table->string('adaption_type')->nullable()->comment('适配类型[原生适配,自研适配,开源适配,项目适配]');
            $table->string('test_type')->nullable()->comment('测试方式[厂商自测,视频复测,远程测试,麒麟适配测试]');
            $table->boolean('kylineco')->nullable()->comment('是否上传生态网站');
            $table->boolean('appstore')->nullable()->comment('是否上架软件商店');
            $table->boolean('iscert')->nullable()->comment('是否互认证');
            $table->timestamp('start_time')->nullable()->comment('适配开始时间');
            $table->timestamp('complete_time')->nullable()->comment('适配完成时间');
            $table->string('comment')->nullable()->comment('备注');
            
            $table->timestamps();

            $table->unique(['softwares_id', 'releases_id', 'chips_id']);
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
