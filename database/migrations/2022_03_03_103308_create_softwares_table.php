<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftwaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('softwares', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique()->comment('软件名');

            $table->foreignId('manufactors_id')
                  ->comment('厂商名称')
                  ->constrained();
            $table->string('version')->comment('软件版本号');
            $table->string('packagename')->comment('包名');
            $table->foreignId('types_id')
                  ->comment('软件分类')
                  ->constrained();
            $table->string('kernel_version')->nullable()->comment('内核引用版本');
            $table->string('crossover_version')->nullable()->comment('Crossover版本');
            $table->string('box86_version')->nullable()->comment('Box86版本');
            $table->string('bd')->comment('Business Development, 生态负责人');
            $table->string('am')->nullable()->comment('Adaption Manager, 适配负责人');
            $table->string('tsm')->nullable()->comment('Technical Support Manager, 技术支撑负责人');
            $table->string('comment')->nullable()->comment('软件描述');
                              
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
        Schema::dropIfExists('softwares');
    }
}
