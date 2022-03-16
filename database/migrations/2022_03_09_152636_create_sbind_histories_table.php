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

            $table->foreignId('pbinds_id')
                ->comment('PBind表ID')
                ->constrained();
            $table->foreignId('statuses_old')
                ->comment('修改前适配状态')
                ->constrained('statuses', 'id');
            $table->foreignId('statuses_new')
                ->comment('修改后适配状态')
                ->constrained('statuses', 'id');
            // $table->foreignId('admin_users_id')
            //     ->comment('当前适配状态责任人')
            //     ->constrained();

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
