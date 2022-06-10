<?php

use App\Models\AdminUser;
use App\Models\Oem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOemAdminUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oems', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_user_id')->nullable()->comment('当前适配状态责任人')->after('user_name');
        });

        $oems = Oem::where('user_name', '!=', null)->get();
        foreach ($oems as $oem) {
            $oem->timestamps=false;
            $oem->admin_user_id = AdminUser::where('name', $oem->user_name)->pluck('id')->first();
            $oem->save();
        }

        Schema::table('oems', function (Blueprint $table) {
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
        Schema::table('oems', function (Blueprint $table) {
            $table->string('user_name')->nullable()->comment('当前适配状态责任人')->after('admin_user_id');
        });
        
        $oems = Oem::where('admin_user_id', '!=', null);
        foreach ($oems as $oem) {  
            $oem->user_name = AdminUser::where('id', $oem->admin_user_id)->pluck('name')->first();
            $oem->save();
        }

        Schema::table('oems', function (Blueprint $table) {
            $table->dropColumn('admin_user_id');
        });
    }
}
