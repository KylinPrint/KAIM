<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForAudit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pbinds', function (Blueprint $table) {
            $table->string('statuses_comment')->nullable()->comment('适配状态变更说明')->after('statuses_id');
        });

        Schema::table('sbinds', function (Blueprint $table) {
            $table->string('statuses_comment')->nullable()->comment('适配状态变更说明')->after('statuses_id');
        });

        Schema::table('p_requests', function (Blueprint $table) {
            $table->string('status_comment')->nullable()->comment('需求状态变更说明')->after('status');
        });

        Schema::table('s_requests', function (Blueprint $table) {
            $table->string('status_comment')->nullable()->comment('需求状态变更说明')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pbinds', function (Blueprint $table) {
            $table->dropColumn('statuses_comment');
        });

        Schema::table('sbinds', function (Blueprint $table) {
            $table->dropColumn('statuses_comment');
        });

        Schema::table('p_requests', function (Blueprint $table) {
            $table->dropColumn('status_comment');
        });

        Schema::table('s_requests', function (Blueprint $table) {
            $table->dropColumn('status_comment');
        });
    }
}
