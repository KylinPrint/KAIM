<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePrequestsOsSubversion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('p_requests', function (Blueprint $table) {
            $table->string('os_subversion')->nullable()->comment('操作系统小版本号')->after('release_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('p_requests', function (Blueprint $table) {
            $table->dropColumn('os_subversion');
        });
    }
}
