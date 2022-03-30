<?php

namespace App\Admin\Metrics;

use App\Models\Pbind;
use App\Models\Peripheral;
use App\Models\Sbind;
use App\Models\Software;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Donut;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataCount extends Donut
{
    protected $labels = ['产品数据总数','适配数据总数'];

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $color = Admin::color();
        $colors = [$color->dark90(),$color->blue1(), $color->alpha('blue2', 0.5),$color->blue2(),$color->blue1()];

        $this->title('适配数据总数统计');
        // $this->dropdown([
        //     '7' => 'Last 7 Days',
        //     '30' => 'Last Month',
        //     '365' => 'Last Year',
        // ]);
        $this->chartLabels($this->labels);
        // 设置图表颜色
        $this->chartColors($colors);

        $this->chartHeight(150);

        //显示图标百分百
        // $this->chart([
        //     'dataLabels' => [
        //         'enabled' => true,
        //         'formatter' => JavaScript::make(
        //             <<<JS
        //             function (val,options){
        //                 return val.toFixed(1)+'%';
        //             }
        //             JS
        //         )
        //     ]
        // ]);

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
        $curOption = $request->get('option')?:'7';
        $curTime = now();
        $curTimeBefor = now()->subDays($curOption)->toDateTimeString();

        // $AddNum = count(Pbind::all()->whereBetween('created_at',[$curTimeBefor,$curTime]));
        $a1 = 
            count(Peripheral::all())+count(Software::all());

        $a2 = 
        count(Sbind::all())+count(Pbind::all());
 

        $this->withContent($a1,$a2);

        // 图表数据

        //$this->withChart([$a1,$a2,$a3,$a4,$a5]);


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
    protected function withContent($a1,$a2)
    {
        // $content = parent::render();

        $AddColor = Admin::color()->dark90();
        $blue = Admin::color()->alpha('blue2', 0.5);
        $blue1 = Admin::color()->blue1();

        $style = 'margin-bottom: 8px';
        $labelWidth = 120;

        return $this->content(
            <<<HTML
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $AddColor"></i> {$this->labels[0]}
    </div>
    <div>{$a1}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $blue"></i> {$this->labels[1]}
    </div>
    <div>{$a2}</div>
</div>

HTML
        );
    }
}
