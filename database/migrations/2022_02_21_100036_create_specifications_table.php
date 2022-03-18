<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('参数名称');

            $table->foreignId('types_id')
                  ->comment('所属分类')
                  ->constrained();
            $table->boolean('isrequired')->comment('是否必填');
            $table->unsignedSmallInteger('field')->comment('参数类型[0=>文本,1=>数字,2=>布尔]');

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
        Schema::dropIfExists('specifications');
    }
}
