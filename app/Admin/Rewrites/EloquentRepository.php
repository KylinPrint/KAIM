<?php

namespace App\Admin\Rewrites;
use App\Traits\CopyObjectAttributes;
use Dcat\Admin\Repositories\EloquentRepository as BaseClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentRepository extends BaseClass
{
    use CopyObjectAttributes;
    /**
     * 查询Grid表格数据.
     *
     * @param  Grid\Model  $model
     * @return Builder
     */
    public function get(\Dcat\Admin\Grid\Model $model)
    {
        /** @var Model $model */
        $this->setSort($model);
        $this->setPaginate($model);

        $query = $this->newQuery();

        if ($this->relations) {
            $query->with($this->relations);
        }
        // 排除get方法，只获取builder
        $model->setQueries($model->getQueries()->filter(function($v){
            return $v['method']!=='get';
        }));
        //dd($query);
        return $model->apply($query, true, $this->getGridColumns());
    }
}