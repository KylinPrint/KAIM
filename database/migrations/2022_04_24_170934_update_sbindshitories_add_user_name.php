<?php

use App\Models\AdminUser;
use App\Models\SbindHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSbindshitoriesAddUserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sbind_histories', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('admin_users_id');
        });
        $sbindhistories = SbindHistory::where('admin_users_id','!=','')->get();
        foreach ($sbindhistories as $sbindhistory)
        {  
            $sbindhistory->user_name = AdminUser::where('id', $sbindhistory->admin_users_id)->pluck('name')->first();
            $sbindhistory->save();
        }
        Schema::table('sbind_histories', function (Blueprint $table) {     
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
        Schema::table('sbind_histories', function (Blueprint $table) { 
            $table->BigInteger('admin_users_id')->nullable()->comment('当前适配状态责任人')->after('user_name');
        });   
        $sbindhistories = sbindHistory::where('user_name','!=','')->get();
        foreach ($sbindhistories as $sbindhistory)
        {  
            $sbindhistory->user_name = AdminUser::where('name', $sbindhistory->user_name)->pluck('id')->first();
            $sbindhistory->save();
        }
        Schema::table('sbind_histories', function (Blueprint $table) {    
            $table->dropColumn('user_name');
        });
    }
}
