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

            $table->string('name')->comment('软件名');

            $table->foreignId('manufactors_id')
                  ->comment('厂商名称')
                  ->constrained();
            $table->string('version')->comment('软件版本');
            $table->string('types_id')
                  ->comment('软件分类')
                  ->constrained();
                              
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
