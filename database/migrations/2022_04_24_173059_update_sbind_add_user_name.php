<?php

use App\Models\AdminUser;
use App\Models\Sbind;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSbindAddUserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sbinds', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('admin_users_id');
        });
        $sbinds = Sbind::where('admin_users_id','!=','')->get();
        foreach ($sbinds as $sbind)
        {  
            $sbind->user_name = AdminUser::where('id', $sbind->admin_users_id)->pluck('name')->first();
            $sbind->save();
        }
        Schema::table('sbinds', function (Blueprint $table) {     
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
        Schema::table('sbinds', function (Blueprint $table) { 
            $table->BigInteger('admin_users_id')->nullable()->comment('当前适配状态责任人')->after('user_name');
        });   
        $sbinds = Sbind::where('user_name','!=','')->get();
        foreach ($sbinds as $sbind)
        {  
            $sbind->user_name = AdminUser::where('name', $sbind->user_name)->pluck('id')->first();
            $sbind->save();
        }
        Schema::table('sbinds', function (Blueprint $table) {    
            $table->dropColumn('user_name');
        });
    }
}
