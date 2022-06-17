<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOemsOtherNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('oems', function (Blueprint $table) {
            $table->string('otypes_id')->comment('分类')->change();
            $table->string('source')->nullable()->comment('引入来源')->change();
            $table->foreignId('status_id')->nullable()->comment('当前适配状态')->change();
            $table->string('user_name')->nullable()->comment('当前适配状态责任人')->change();
            $table->boolean('kylineco')->nullable()->comment('是否上传生态网站')->change();
            $table->boolean('iscert')->nullable()->comment('是否互认证')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
