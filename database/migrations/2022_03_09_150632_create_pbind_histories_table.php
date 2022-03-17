<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePbindHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pbind_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pbind_id')
                ->comment('PBind表ID')
                ->constrained();
            $table->foreignId('status_old')
                ->comment('修改前适配状态')
                ->constrained('statuses', 'id');
            $table->foreignId('status_new')
                ->comment('修改后适配状态')
                ->constrained('statuses', 'id');
            // $table->foreignId('admin_users_id')
            //     ->comment('当前适配状态责任人')
            //     ->constrained();

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
        Schema::dropIfExists('pbind_histories');
    }
}