<?php

use App\Models\AdminUser;
use App\Models\PRequestHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePrequestsHistoriesAddUserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('p_request_histories', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('operator');
        });
        $prequesthistories = PRequestHistory::where('operator','!=','')->get();
        foreach ($prequesthistories as $prequesthistory)
        {  
            $prequesthistory->user_name = AdminUser::where('id', $prequesthistory->admin_users_id)->pluck('name')->first();
            $prequesthistory->save();
        }
        Schema::table('p_request_histories', function (Blueprint $table) {     
            $table->dropColumn('operator');
            $table->string('user_name', 191)->comment('当前适配状态责任人')->change();
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
