<?php

use App\Models\AdminUser;
use App\Models\PbindHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePbindshitoriesAddUserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('pbind_histories', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('admin_users_id');
        });
        $pbindhistories = PbindHistory::where('admin_users_id','!=','')->get();
        foreach ($pbindhistories as $pbindhistory)
        {  
            $pbindhistory->user_name = AdminUser::where('id', $pbindhistory->admin_users_id)->pluck('name')->first();
            $pbindhistory->save();
        }
        Schema::table('pbind_histories', function (Blueprint $table) {     
            $table->dropColumn('admin_users_id');
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
        Schema::table('pbind_histories', function (Blueprint $table) { 
            $table->BigInteger('admin_users_id')->nullable()->comment('当前适配状态责任人')->after('user_name');
        });   
        $pbindhistories = PbindHistory::where('user_name','!=','')->get();
        foreach ($pbindhistories as $pbindhistory)
        {  
            $pbindhistory->user_name = AdminUser::where('name', $pbindhistory->user_name)->pluck('id')->first();
            $pbindhistory->save();
        }
        Schema::table('pbind_histories', function (Blueprint $table) {    
            $table->dropColumn('user_name');
        });
    }
}
