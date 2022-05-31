<?php

namespace App\Admin\Rewrites;

use App\Traits\CopyObjectAttributes;
use Illuminate\Database\Eloquent\Builder as Base;
class Builder extends Base
{
    use CopyObjectAttributes;

    /**
     * Chunk the results of the query.
     *
     * @param  int  $count
     * @param  callable  $callback
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        $a = $count;
        $this->enforceOrderBy();
        if (!is_null($this->query->limit) && !is_null($this->query->offset)) {
            $page = $this->query->offset / $this->query->limit + 1;
            $count = $this->query->limit;
        } else {
            $page = 1;
        }
        do {

            $results = $this->forPage($page, $count)->get();
            $countResults = $results->count();

            if ($countResults == 0) {
                break;
            }

            if ($callback($results, $page) === false) {
                return false;
            }

            unset($results);

            $page++;

        } while ($countResults == $a);

        return true;
    }


}