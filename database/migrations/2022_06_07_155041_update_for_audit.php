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
            $table->string('statuses_comment')->nullable()->after('statuses_id');
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
    }
}
