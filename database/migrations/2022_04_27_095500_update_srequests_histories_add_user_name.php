<?php

use App\Models\AdminUser;
use App\Models\SRequestHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSrequestsHistoriesAddUserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('s_request_histories', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('operator');
        });
        $srequesthistories = SRequestHistory::where('operator','!=','')->get();
        foreach ($srequesthistories as $srequesthistory)
        {  
            $srequesthistory->user_name = AdminUser::where('id', $srequesthistory->admin_users_id)->pluck('name')->first();
            $srequesthistory->save();
        }
        Schema::table('s_request_histories', function (Blueprint $table) {     
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
