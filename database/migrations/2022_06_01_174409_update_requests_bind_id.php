<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRequestsBindId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('s_requests', function (Blueprint $table) {
            $table->renameColumn('sbind_id', 'sbinds_id');
        });

        Schema::table('p_requests', function (Blueprint $table) {
            $table->renameColumn('pbind_id', 'pbinds_id');
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
