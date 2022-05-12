<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTypesAddOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('types', function (Blueprint $table) {     
            $table->unsignedBigInteger('order')->comment('排序')->after('parent')->default(0);
        });
        Schema::table('stypes', function (Blueprint $table) {     
            $table->unsignedBigInteger('order')->comment('排序')->after('parent')->default(0);
        });
        Schema::table('otypes', function (Blueprint $table) {     
            $table->unsignedBigInteger('order')->comment('排序')->after('parent')->default(0);
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
        Schema::table('types', function (Blueprint $table) {     
            $table->dropColumn('order');
        });
        Schema::table('stypes', function (Blueprint $table) {     
            $table->dropColumn('order');
        });
        Schema::table('otypes', function (Blueprint $table) {     
            $table->dropColumn('order');
        });
    }
}
