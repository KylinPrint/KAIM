<?php

use App\Models\AdminUser;
use App\Models\Pbind;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePbindAddUserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('pbinds', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('admin_users_id');
        });
        $pbinds = Pbind::where('admin_users_id','!=','')->get();
        foreach ($pbinds as $pbind)
        {  
            $pbind->user_name = AdminUser::where('id', $pbind->admin_users_id)->pluck('name')->first();
            $pbind->save();
        }
        Schema::table('pbinds', function (Blueprint $table) {     
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
        Schema::table('pbinds', function (Blueprint $table) { 
            $table->BigInteger('admin_users_id')->nullable()->comment('当前适配状态责任人')->after('user_name');
        });   
        $pbinds = Pbind::where('user_name','!=','')->get();
        foreach ($pbinds as $pbind)
        {  
            $pbind->user_name = AdminUser::where('name', $pbind->user_name)->pluck('id')->first();
            $pbind->save();
        }
        Schema::table('pbind', function (Blueprint $table) {    
            $table->dropColumn('user_name');
        });
    }
}
