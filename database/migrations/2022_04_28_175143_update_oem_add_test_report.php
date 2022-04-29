<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOemAddTestReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('oems', function (Blueprint $table) {     
            $table->integer('test_report')->nullable()->comment('是否有测试报告')->after('iscert');
            $table->string('certificate_NO')->nullable()->comment('证书编号')->after('iscert');
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
        Schema::table('oem', function (Blueprint $table) {     
            $table->dropColumn('test_report');
            $table->dropColumn('certificate_NO');
        });
    }
}
