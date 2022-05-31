<?php

namespace App\Admin\Rewrites;

use App\Traits\CopyObjectAttributes;
use Dcat\Admin\Grid\Filter as BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class Filter extends BaseFilter
{
    use CopyObjectAttributes;

    public function getScopeConditions()
    {
        if ($scope = $this->getCurrentScope()) {
            return $scope->condition();
        }

        return [];
    }
    /**
     * Execute the filter with conditions.
     * @param $filter
     * @return Builder
     */
    public function execute()
    {
        $conditions = array_merge(
            $this->getConditions(),
            $this->getScopeConditions()
        );

        $this->model->addConditions($conditions);

        $model=new \App\Admin\Rewrites\Model($this->model);
        $query = $model->fetch();

        return $query;
    }

}