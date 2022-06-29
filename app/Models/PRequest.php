<?php

namespace App\Models;

use Dcat\Admin\Admin;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Models\Audit;

class PRequest extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	use HasDateTimeFormatter;

	protected $fillable = 
	[
		'source',
		'manufactor',
		'brand',
		'name',
		'type_id',
		'industry',
		'release_id',
		'os_subversion',
		'chip_id',
		'project_name',
		'amount',
		'project_status',
		'level',
		'manufactor_contact',
		'et',
		'creator',
		'requester_name',
		'requester_contact',
		'status',
		'statuses_comment',
		'bd_id',
		'pbind_id',
		'comment',
	];

	// 我创建的
	public function scopeCreated($query)
	{
		return $query->where('creator', Admin::user()->id);
	}

	// 我参与的
	public function scopeRelated($query)
	{
		// 筛选PRequest相关的审计
		$audit_prequest = Audit::where('auditable_type', 'App\Models\PRequest');

		$related = array_unique(array_merge(
			// 当前用户编辑过的
			$audit_prequest->where('admin_user_id', Admin::user()->id)->pluck('auditable_id')->toarray(),
			PRequest::where('bd_id', Admin::user()->id)->pluck('id')->toArray(),
			// 当前用户曾经是BD的
			$audit_prequest->whereJsonContains('old_values->bd_id', Admin::user()->id)->pluck('auditable_id')->toarray(),
		));

		return $query
			// 当前用户是BD的
			->where('bd_id', Admin::user()->id)
			->orWhereIn('id', $related);
	}

	// 我的待办
	public function scopeTodo($query)
	{
		return $query
			// 已提交/处理中/验证未通过/重新处理中 的数据显示给BD
			->where(function ($query) {
				$query->where('bd_id', Admin::user()->id)
					->whereIn('status', ['已提交', '处理中', '验证未通过', '重新处理中']);
			})
			// 已提交/处理中/已处理/验证未通过/重新处理中/无法处理/已拒绝 的数据显示给创建人
			->orWhere(function ($query) {
				$query->where('creator', Admin::user()->id)
					->whereIn('status', ['已提交', '处理中', '已处理', '验证未通过', '重新处理中', '无法处理', '已拒绝']);
			});
	}

	public function type() { return $this->belongsTo(Type::class); }

	public function release() { return $this->belongsTo(Release::class); }

	public function chip() { return $this->belongsTo(Chip::class); }

	public function bd() { return $this->belongsTo(AdminUser::class); }

	public function pbinds() { return $this->belongsTo(Pbind::class, 'pbind_id'); }
}
