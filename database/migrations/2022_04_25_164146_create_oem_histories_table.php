<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oem_histories', function (Blueprint $table) {
            $table->foreignId('oem_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

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
        Schema::dropIfExists('oem_histories');
    }
}
