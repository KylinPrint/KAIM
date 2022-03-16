<?php

namespace App\Admin\Metrics;

use App\Models\Pbind;
use Dcat\Admin\Admin;
use Dcat\Admin\Support\JavaScript;
use Dcat\Admin\Widgets\Metrics\Donut;
use Illuminate\Http\Request;

class DataAdd extends Donut
{
    protected $labels = ['新增'];

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $color = Admin::color();
        $colors = [$color->dark90(),$color->blue1(), $color->alpha('blue2', 0.5),$color->blue2()];

        $this->title('New Data');
        $this->dropdown([
            '7' => 'Last 7 Days',
            '30' => 'Last Month',
            '365' => 'Last Year',
        ]);
        $this->chartLabels($this->labels);
        // 设置图表颜色
        $this->chartColors($colors);

        //显示图标百分百
        $this->chart([
            'dataLabels' => [
                'enabled' => true,
                'formatter' => JavaScript::make(
                    <<<JS
                    function (val,options){
                        return val.toFixed(1)+'%';
                    }
                    JS
                )]
            ]);
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
        $curOption = $request->get('option');
        $curTime = now();
        $curTimeBefor = now()->subDays($curOption);

        $AddNum = count(Pbind::all()->whereBetween('created_at',[$curTimeBefor,$curTime]));
 
        
        $this->withContent($AddNum);

        // 图表数据

        $this->withChart([$AddNum]);

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
    protected function withContent($AddNum)
    {
        // $content = parent::render();

        $AddColor = Admin::color()->dark90();
        // $blue = Admin::color()->alpha('blue2', 0.5);
        // $blue1 = Admin::color()->blue1();

        $style = 'margin-bottom: 8px';
        $labelWidth = 120;

        return $this->content(
            <<<HTML
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $AddColor"></i> {$this->labels[0]}
    </div>
    <div>{$AddNum}</div>
</div>


HTML
        );
    }
}
