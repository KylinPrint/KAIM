<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePrequestsUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //删除再添加
        Schema::table('p_requests', function (Blueprint $table) {
            $table->dropUnique('p_requests_brand_name_release_id_chip_id_unique');
            $table->string('manufactor', 191)->comment('厂商名称')->change();
            $table->string('brand', 191)->comment('品牌名称')->change();
            $table->string('name', 191)->comment('外设名称')->change();
            $table->unique(['manufactor','brand', 'name', 'release_id', 'chip_id'],'p_requests_unique');
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
