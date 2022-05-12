<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSrequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s_requests', function (Blueprint $table) {
            $table->id();
            $table->string('source')->comment('需求来源');
            $table->string('manufactor')->comment('厂商名称');
            $table->string('name')->comment('产品名称');

            $table->foreignId('stype_id')
                  ->comment('软件分类')
                  ->constrained();
            $table->string('industry')->comment('涉及行业');
            $table->foreignId('release_id')
                  ->comment('操作系统版本ID')
                  ->constrained();
            $table->foreignId('chip_id')
                  ->comment('芯片ID')
                  ->constrained();
            $table->string('project_name')->nullable()->comment('项目名称');
            $table->string('amount')->nullable()->comment('涉及数量');
            $table->string('project_status')->nullable()->comment('项目状态');
            $table->string('level')->comment('紧急程度');
            $table->string('manufactor_contact')->nullable()->comment('厂商联系方式');
            $table->date('et')->comment('期望完成日期');
            $table->string('requester_name')->comment('需求提出人');
            $table->string('requester_contact')->comment('需求提出人联系方式');
            $table->string('status')->comment('处理状态[已提交,处理中,已处理,暂停处理,已拒绝,已关闭]');
            $table->unsignedBigInteger('bd_id')->comment('生态负责人');
            $table->foreignId('sbind_id')
                ->nullable()
                ->comment('软件关联信息')
                ->constrained();
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
        Schema::dropIfExists('s_requests');
    }
}
