<?php

namespace App\Models;

use Dcat\Admin\Admin;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

class Sbind extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	use HasDateTimeFormatter;
	
	protected $table = 'sbinds';

	protected $fillable = 
	[
		'softwares_id',
		'releases_id',
		'os_subversion',
		'chips_id',
		'adapt_source',
		'adapted_before',
		'statuses_id',
		'statuses_comment',
		'admin_user_id',
		'softname',
		'solution',
		'solution_name',
		'class',
		'adaption_type',
		'test_type',
		'kylineco',
		'appstore',
		'iscert',
		'test_report',
		'certificate_NO',
		'comment',
	];

	// 我创建的
	public function scopeCreated($query)
	{
		$created = Audit::where([
			'admin_user_id'     => Admin::user()->id,
			'event'             => 'created',
			'auditable_type'    => 'App\Models\Sbind',
		])->pluck('auditable_id')->toarray();
		
		return $query->whereIn('id', $created);
	}

	// 我参与的
	public function scopeRelated($query)
	{
		// 筛选SBind相关的审计
		$audit_sbind = Audit::where('auditable_type', 'App\Models\Sbind');

		$related = array_unique(array_merge(
			// 当前用户编辑过的
			$audit_sbind->where('admin_user_id', Admin::user()->id)->pluck('auditable_id')->toarray(),
			// 当前用户曾经是责任人的
			$audit_sbind->whereJsonContains('old_values->admin_user_id', Admin::user()->id)->pluck('auditable_id')->toarray(),
		));

		return $query
			// 当前用户是责任人的
			->where('admin_user_id', Admin::user()->id)
			->orWhereIn('id', $related);
	}

	// 我的待办
	public function scopeTodo($query)
	{
		return $query
			// 当前适配状态责任人为当前登录用户的
			->where('admin_user_id', Admin::user()->id)
			->whereNot(function ($query) {
				// 过滤掉状态为"证书已归档",且"是否互认证"为"是"的
				$query->where('statuses_id', Status::where('name', '证书已归档')->pluck('id')->first())->where('iscert', 1);
			})
			->whereNot(function ($query) {
				// 过滤掉状态为"适配成果已上架至软件商店",且"是否互认证"为"否","是否上架软件商店"为"是"的
				$query->where('statuses_id', Status::where('name', '适配成果已上架至软件商店')->pluck('id')->first())->where('iscert', 0)->where('appstore', 1);
			})
			->whereNot(function ($query) {
				// 过滤掉状态为"厂商暂无适配计划" "适配成果已下架软件商店" "自研适配方案新增或导入" 的
				$query->whereHas('statuses', function (Builder $query) {
					$query->whereIn('name', ['厂商暂无适配计划', '麒麟自研适配方案，内部已验证通过', '麒麟自研适配方案，待内部验证', '适配成果已下架软件商店']);
				});
			});
	}

	public function softwares()
    {
        return $this->belongsTo(Software::class);
    }

	public function releases()
    {
        return $this->belongsTo(Release::class);
    }

	public function chips()
	{
		return $this->belongsTo(Chip::class);
	}

	public function statuses()
	{
		return $this->belongsTo(Status::class);
	}

	public function admin_users()
	{
		return $this->belongsTo((AdminUser::class));
	}
}
