<?php

use App\Models\AdminUser;
use App\Models\OemHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOemHistoriesAddUserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('oem_histories', function (Blueprint $table) {     
            $table->string('user_name', 191)->nullable()->comment('当前适配状态责任人')->after('operator');
        });
        $oemhistories = OemHistory::where('operator','!=','')->get();
        foreach ($oemhistories as $oemhistory)
        {  
            $oemhistory->user_name = AdminUser::where('id', $oemhistory->admin_users_id)->pluck('name')->first();
            $oemhistory->save();
        }
        Schema::table('oem_histories', function (Blueprint $table) {     
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
