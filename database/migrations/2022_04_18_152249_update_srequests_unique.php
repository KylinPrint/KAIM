<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSrequestsUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //删除再添加
        Schema::table('s_requests', function (Blueprint $table) {
            $table->dropUnique('s_requests_manufactor_name_release_id_chip_id_unique');
            $table->string('manufactor', 191)->comment('厂商名称')->change();
            $table->string('version', 191)->comment('软件版本')->after('name');
            $table->string('name', 191)->comment('软件名称')->change();
            $table->unique(['manufactor','version', 'name', 'release_id', 'chip_id'],'s_requests_unique');
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
