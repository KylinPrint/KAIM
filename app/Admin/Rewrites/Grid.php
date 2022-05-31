<?php
namespace App\Admin\Rewrites;

use App\Traits\CopyObjectAttributes;
use Dcat\Admin\Grid as BaseGrid;
use Illuminate\Database\Eloquent\Builder;

class Grid extends BaseGrid
{
  use CopyObjectAttributes;

  /**
 * Process the grid filter. * @param Grid $grid
 * @return Builder
  */
  public function processFilter2(\App\Admin\Rewrites\Grid $grid)
 {
  $this->callBuilder();
  $this->handleExportRequest();

  $this->applyQuickSearch();
  $this->applyColumnFilter();
  $this->applySelectorQuery();
  $filter=new Filter($grid->filter());

  return $filter->execute();
  }
}
