<?php
namespace App\Admin\Rewrites;

use App\Traits\CopyObjectAttributes;
use Dcat\Admin\Exception\AdminException;
use Illuminate\Database\Eloquent\Builder;

class Model extends \Dcat\Admin\Grid\Model
{
    use CopyObjectAttributes;

    /**
     * @return Builder
     * @throws \Exception
     */
    public function fetch()
    {

        $repository = new EloquentRepository($this->repository);
        $results = $repository->get($this);
        if (!is_null($results)) {
            return $results;
        }

        throw new AdminException('Grid query error');
    }
}