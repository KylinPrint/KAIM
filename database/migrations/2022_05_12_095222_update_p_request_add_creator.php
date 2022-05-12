<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePRequestAddCreator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('p_requests', function (Blueprint $table) {     
            $table->unsignedBigInteger('creator')->nullable()->comment('需求创建人')->after('et');
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
            $table->dropColumn('creator');
        });
    }
}
