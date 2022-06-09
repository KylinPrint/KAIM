<?php

use App\Models\AdminUser;
use App\Models\Pbind;
use App\Models\Sbind;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAdminUserId extends Migration
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
            $table->unsignedBigInteger('admin_user_id')->nullable()->comment('当前适配状态责任人')->after('user_name');
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

        Schema::table('pbinds', function (Blueprint $table) { 
            $table->unsignedBigInteger('admin_user_id')->nullable()->comment('当前适配状态责任人')->after('user_name');
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('pbinds', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('admin_user_id');
        });
        $pbinds = Pbind::where('admin_user_id','!=','')->get();
        foreach ($pbinds as $pbind)
        {  
            $pbind->user_name = AdminUser::where('id', $pbind->admin_user_id)->pluck('name')->first();
            $pbind->save();
        }
        Schema::table('pbinds', function (Blueprint $table) {     
            $table->dropColumn('admin_user_id');
        });

        Schema::table('sbinds', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('admin_user_id');
        });
        $sbinds = Sbind::where('admin_user_id','!=','')->get();
        foreach ($sbinds as $sbind)
        {  
            $sbind->user_name = AdminUser::where('id', $sbind->admin_user_id)->pluck('name')->first();
            $sbind->save();
        }
        Schema::table('sbinds', function (Blueprint $table) {     
            $table->dropColumn('admin_user_id');
        });
    }
}
