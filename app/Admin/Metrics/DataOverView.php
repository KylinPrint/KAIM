<?php

namespace App\Admin\Metrics;

use App\Models\Oem;
use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Sbind;
use App\Models\Software;
use Dcat\Admin\Widgets\Metrics\Card;

class DataOverView extends Card
{
    /**
     * 初始化卡片内容
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->title('适配数据统计');
        $this->contentWidth(0, 12);
        $this->height(285);
    }

    /**
     * 查询数据
     *
     * @param int $range
     *
     * @return array
     */
    public function query() {
        $peripheral_count = Peripheral::count();
        $software_count = Software::count();
        $product_count = $peripheral_count + $software_count;

        $pbind_count = Pbind::count();
        $sbind_count = Sbind::count();
        $oem_count = Oem::count();
        $bind_count = $pbind_count + $sbind_count + $oem_count;

        return [
            'peripheral'    => $peripheral_count,
            'software'      => $software_count,
            'product'       => $product_count,
            'pbind'         => $pbind_count,
            'sbind'         => $sbind_count,
            'oem'           => $oem_count,
            'bind'          => $bind_count,
        ];
    }

    /**
     * 写入数据.
     *
     * @return void
     */
    public function handle()
    {
        $this->withContent();
    }

    /**
     * 设置卡片内容.
     *
     * @return $this
     */
    public function withContent()
    {
        $data = $this->query();
        return $this->content(
            <<<HTML
<div class="row text-center mx-0" style="width: 100%">
  <div class="col-6 border-top border-right d-flex align-items-between flex-column py-1">
      <p class="mb-50">产品数据</p>
      <p class="font-lg-1 text-bold-700 mb-50">{$data['product']}</p>
  </div>
  <div class="col-6 border-top d-flex align-items-between flex-column py-1">
      <p class="mb-50">适配数据</p>
      <p class="font-lg-1 text-bold-700 mb-50">{$data['bind']}</p>
  </div>
  <div class="col-3 border-top border-right border-bottom d-flex align-items-between flex-column py-1">
      <p class="mb-50">外设产品数据</p>
      <h3 class="text-bold-700 mb-50">{$data['peripheral']}</h3>
  </div>
  <div class="col-3 border-top border-right border-bottom d-flex align-items-between flex-column py-1">
      <p class="mb-50">软件产品数据</p>
      <h3 class="text-bold-700 mb-50">{$data['software']}</h3>
  </div>
  <div class="col-2 border-top border-right border-bottom d-flex align-items-between flex-column py-1">
      <p class="mb-50">外设适配数据</p>
      <h3 class="text-bold-700 mb-50">{$data['pbind']}</h3>
  </div>
  <div class="col-2 border-top border-right border-bottom d-flex align-items-between flex-column py-1">
      <p class="mb-50">软件适配数据</p>
      <h3 class="text-bold-700 mb-50">{$data['sbind']}</h3>
  </div>
  <div class="col-2 border-top border-bottom d-flex align-items-between flex-column py-1">
      <p class="mb-50">整机适配数据</p>
      <h3 class="text-bold-700 mb-50">{$data['oem']}</h3>
  </div>
</div>
HTML
        );
    }
}
