<?php

use App\Models\AdminUser;
use App\Models\SbindHistory;
use App\Models\PbindHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (SbindHistory::whereNull('status_old')->oldest()->get() as $sbindhistory) {
            DB::table('audits')->insert([
                'admin_user_id'     => AdminUser::where('name', $sbindhistory->user_name)->pluck('id')->first(),
                'event'             => 'created',
                'auditable_type'    => 'App\Models\Sbind',
                'auditable_id'      => $sbindhistory->sbind_id,
                'old_values'        => "[]",
                'new_values'        => "[]",
                'created_at'        => $sbindhistory->created_at,
                'updated_at'        => $sbindhistory->updated_at,
            ]);
        }

        foreach (PbindHistory::whereNull('status_old')->oldest()->get() as $pbindhistory) {
            DB::table('audits')->insert([
                'admin_user_id'     => AdminUser::where('name', $pbindhistory->user_name)->pluck('id')->first(),
                'event'             => 'created',
                'auditable_type'    => 'App\Models\Pbind',
                'auditable_id'      => $pbindhistory->pbind_id,
                'old_values'        => "[]",
                'new_values'        => "[]",
                'created_at'        => $pbindhistory->created_at,
                'updated_at'        => $pbindhistory->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
};
