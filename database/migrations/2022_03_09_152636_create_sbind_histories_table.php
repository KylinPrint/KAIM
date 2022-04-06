<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSbindHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sbind_histories', function (Blueprint $table) {
            $table->id();

            // SBindController脑瘫代码的后遗症
            $table->unsignedBigInteger('sbind_id')->comment('SBind表ID');
            $table->foreignId('status_old')
                ->nullable()
                ->comment('修改前适配状态')
                ->constrained('statuses', 'id');
            $table->foreignId('status_new')
                ->comment('修改后适配状态')
                ->constrained('statuses', 'id');
            $table->unsignedBigInteger('admin_users_id')->nullable()->comment('当前适配状态责任人');
            
            $table->string('comment')->nullable()->comment('状态变更说明');

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
        Schema::dropIfExists('sbind_histories');
    }
}
