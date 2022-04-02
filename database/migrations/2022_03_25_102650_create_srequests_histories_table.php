<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSrequestsHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s_request_histories', function (Blueprint $table) {
            $table->foreignId('s_request_id')
                  ->comment('SBind表ID')
                  ->constrained();
            $table->string('status_old')->comment('修改前状态');
            $table->string('status_new')->comment('修改后状态');
            $table->unsignedBigInteger('operator')->comment('当前状态责任人');
            
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
        Schema::dropIfExists('s_request_histories');
    }
}
