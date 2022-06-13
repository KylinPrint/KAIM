<?php

use App\Models\AdminUser;
use App\Models\PbindHistory;
use App\Models\PRequestHistory;
use App\Models\SbindHistory;
use App\Models\SRequest;
use App\Models\SRequestHistory;
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
        foreach (SbindHistory::oldest()->get() as $sbindhistory) {
            if (! $sbindhistory->status_old) { continue; }
            $old_values['statuses_id'] = $sbindhistory->status_old;
            $new_values['statuses_id'] = $sbindhistory->status_new;
            if ($sbindhistory->comment) {
                $old_values['statuses_comment'] = '';
                $new_values['statuses_comment'] = $sbindhistory->comment;
            }

            DB::table('audits')->insert([
                'admin_user_id'     => AdminUser::where('name', $sbindhistory->user_name)->pluck('id')->first(),
                'event'             => 'updated',
                'auditable_type'    => 'App\Models\Sbind',
                'auditable_id'      => $sbindhistory->sbind_id,
                'old_values'        => json_encode($old_values),
                'new_values'        => json_encode($new_values),
                'created_at'        => $sbindhistory->created_at,
                'updated_at'        => $sbindhistory->updated_at,
            ]);
        }
        unset($old_values, $new_values);

        foreach (PbindHistory::oldest()->get() as $pbindhistory) {
            if (! $pbindhistory->status_old) { continue; }
            $old_values['statuses_id'] = $pbindhistory->status_old;
            $new_values['statuses_id'] = $pbindhistory->status_new;
            if ($pbindhistory->comment) {
                $old_values['statuses_comment'] = '';
                $new_values['statuses_comment'] = $pbindhistory->comment;
            }
            
            DB::table('audits')->insert([
                'admin_user_id'     => AdminUser::where('name', $pbindhistory->user_name)->pluck('id')->first(),
                'event'             => 'updated',
                'auditable_type'    => 'App\Models\Pbind',
                'auditable_id'      => $pbindhistory->pbind_id,
                'old_values'        => json_encode($old_values),
                'new_values'        => json_encode($new_values),
                'created_at'        => $pbindhistory->created_at,
                'updated_at'        => $pbindhistory->updated_at,
            ]);
        }
        unset($old_values, $new_values);

        foreach (PRequestHistory::oldest()->get() as $prequesthistory) {
            if (! $prequesthistory->status_old) { continue; }
            $old_values['status'] = $prequesthistory->status_old;
            $new_values['status'] = $prequesthistory->status_new;
            if ($prequesthistory->comment) {
                $old_values['status_comment'] = '';
                $new_values['status_comment'] = $prequesthistory->comment;
            }
            
            DB::table('audits')->insert([
                'admin_user_id'     => AdminUser::where('name', $prequesthistory->user_name)->pluck('id')->first(),
                'event'             => 'updated',
                'auditable_type'    => 'App\Models\PRequest',
                'auditable_id'      => $prequesthistory->p_request_id,
                'old_values'        => json_encode($old_values),
                'new_values'        => json_encode($new_values),
                'created_at'        => $prequesthistory->created_at,
                'updated_at'        => $prequesthistory->updated_at,
            ]);
        }
        unset($old_values, $new_values);

        foreach (SRequestHistory::oldest()->get() as $srequesthistory) {
            if (! $srequesthistory->status_old) { continue; }
            $old_values['status'] = $srequesthistory->status_old;
            $new_values['status'] = $srequesthistory->status_new;
            if ($srequesthistory->comment) {
                $old_values['status_comment'] = '';
                $new_values['status_comment'] = $srequesthistory->comment;
            }
            
            DB::table('audits')->insert([
                'admin_user_id'     => AdminUser::where('name', $srequesthistory->user_name)->pluck('id')->first(),
                'event'             => 'updated',
                'auditable_type'    => 'App\Models\SRequest',
                'auditable_id'      => $srequesthistory->s_request_id,
                'old_values'        => json_encode($old_values),
                'new_values'        => json_encode($new_values),
                'created_at'        => $srequesthistory->created_at,
                'updated_at'        => $srequesthistory->updated_at,
            ]);
        }
        unset($old_values, $new_values);
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
