<?php

namespace App\Admin\Metrics;

use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Sbind;
use App\Models\Software;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Donut;
use Illuminate\Http\Request;

class DataCount extends Donut
{
    protected $labels = 
    [
        '产品数据总数',
        '外设产品数据总数',
        '软件产品数据总数',
        '适配数据总数',
        '外设适配数据总数',
        '软件适配数据总数'
    ];

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $color = Admin::color();
        $colors = [$color->dark90(),$color->blue1(), $color->alpha('blue2', 0.5),$color->blue2(),$color->blue1()];

        $this->title('适配数据总数统计');
        $this->chartLabels($this->labels);
        // 设置图表颜色
        $this->chartColors($colors);

        $this->chartHeight(150);

        $this->chart->style('margin: 15px 15px 0 0;width: 200px;float:right;');
    }



    /**
     * 渲染模板
     *
     * @return string
     */


    /**
     * 写入数据.
     *
     * @return void
     */
    public function handle(Request $request)
    {
        $peripheral_count = Peripheral::count();
        $software_count = Software::count();
        $product_count = $peripheral_count + $software_count;

        $pbind_count = Pbind::count();
        $sbind_count = Sbind::count();
        $bind_count = $pbind_count + $sbind_count;
 
        $this->withContent(
            $product_count, $peripheral_count, $software_count,
            $bind_count ,$pbind_count ,$sbind_count);
    }

    /**
     * 设置图表数据.
     *
     * @param array $data
     *
     * @return $this
     */
    public function withChart(array $data)
    {
        return $this->chart(['series' => $data]);
    }

    /**
     * 设置卡片头部内容.
     *
     * @param mixed $desktop
     * @param mixed $mobile
     *
     * @return $this
     */
    protected function withContent($a,$a1,$a2,$b,$b1,$b2)
    {
        // $content = parent::render();

        $AddColor = Admin::color()->dark90();
        $blue = Admin::color()->alpha('blue2', 0.5);
        $blue1 = Admin::color()->blue1();

        $style = 'margin-bottom: 8px';
        $labelWidth = 160;

        return $this->content(
            <<<HTML
             
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: 140px">
        <i class="fa fa-circle" style="color: $AddColor"></i> {$this->labels[0]}
    </div>
    <div>{$a}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px;text-indent:0.5em">
        <i class="fa fa-circle" style="color: $blue1"></i> {$this->labels[1]}
    </div>
    <div>{$a1}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px;text-indent:0.5em">
        <i class="fa fa-circle" style="color: $blue1"></i> {$this->labels[2]}
    </div>
    <div>{$a2}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: 140px">
        <i class="fa fa-circle" style="color: $blue"></i> {$this->labels[3]}
    </div>
    <div>{$b}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px;text-indent:0.5em">
        <i class="fa fa-circle" style="color: $blue1"></i> {$this->labels[4]}
    </div>
    <div>{$b1}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px;text-indent:0.5em">
        <i class="fa fa-circle" style="color: $blue1"></i> {$this->labels[5]}
    </div>
    <div>{$b2}</div>
</div>


HTML
        );
    }
}
