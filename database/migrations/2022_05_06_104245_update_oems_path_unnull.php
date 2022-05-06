<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOemsPathUnnull extends Migration
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
            $table->string('patch')->nullable()->comment('补丁包连接')->change();
            $table->renameColumn('manufactor_id', 'manufactors_id');
            $table->renameColumn('type_id', 'otypes_id');
            $table->renameColumn('release_id', 'releases_id');
            $table->renameColumn('chip_id', 'chips_id');
            // $table->foreignId('type_id')->constrained('otypes');
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
