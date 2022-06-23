<?php

use App\Models\PRequest;
use App\Models\SRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function PHPUnit\Framework\isEmpty;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $s_update_arr = SRequest::whereNull('project_name')
            ->orWhereNull('amount')
            ->orWhereNull('project_status')
            ->orWhereNull('manufactor_contact')
            ->orWhereNull('os_subversion')
            ->get();
        
        foreach($s_update_arr as $s_update){
            if(isEmpty($s_update->project_name)){
                $s_update->project_name = '暂无';
            }
            if(isEmpty($s_update->amount)){
                $s_update->amount = '暂无';
            }
            if(isEmpty($s_update->project_status)){
                $s_update->project_status = '实施阶段';
            }
            if(isEmpty($s_update->manufactor_contact)){
                $s_update->manufactor_contact = '暂无';
            }
            if(isEmpty($s_update->os_subversion)){
                $s_update->os_subversion = '暂无';
            }
            $s_update->save();
        }

        Schema::table('s_requests', function (Blueprint $table) {
            $table->string('project_name')->nullable(false)->comment('项目名称')->change();
            $table->string('amount')->nullable(false)->comment('涉及数量')->change();
            $table->string('project_status')->nullable(false)->comment('项目状态')->change();
            $table->string('manufactor_contact')->nullable(false)->comment('厂商联系方式')->change();
            $table->string('os_subversion')->nullable(false)->comment('操作系统小版本号')->change();
        });

        $p_update_arr = PRequest::whereNull('project_name')
            ->orWhereNull('amount')
            ->orWhereNull('project_status')
            ->orWhereNull('manufactor_contact')
            ->orWhereNull('os_subversion')
            ->get();
        
        foreach($p_update_arr as $p_update){
            if(isEmpty($p_update->project_name)){
                $p_update->project_name = '暂无';
            }
            if(isEmpty($p_update->amount)){
                $p_update->amount = '暂无';
            }
            if(isEmpty($p_update->project_status)){
                $p_update->project_status = '实施阶段';
            }
            if(isEmpty($p_update->manufactor_contact)){
                $p_update->manufactor_contact = '暂无';
            }
            if(isEmpty($p_update->os_subversion)){
                $p_update->os_subversion = '暂无';
            }
            $p_update->save();
        }

        Schema::table('p_requests', function (Blueprint $table) {
            $table->string('project_name')->nullable(false)->comment('项目名称')->change();
            $table->string('amount')->nullable(false)->comment('涉及数量')->change();
            $table->string('project_status')->nullable(false)->comment('项目状态')->change();
            $table->string('manufactor_contact')->nullable(false)->comment('厂商联系方式')->change();
            $table->string('os_subversion')->nullable(false)->comment('操作系统小版本号')->change();
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
};
