<?php

namespace App\Admin\Metrics;

use App\Models\Pbind;
use App\Models\Sbind;
use App\Models\Status;
use Dcat\Admin\Admin;
use Dcat\Admin\Support\JavaScript;
use Dcat\Admin\Widgets\Metrics\Donut;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PDataAdd extends Donut
{
    protected $labels = ['未适配','适配中','已适配','待验证','适配暂停'];

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $color = Admin::color();
        $colors = [$color->red(),$color->yellow(), $color->green(),$color->blue(),$color->gray()];

        $this->title('外设新增适配数据');
        $this->dropdown([
            '30' => '30天内',
            '7' => '7天内',
            '365' => '1年内',
        ]);
        $this->chartLabels($this->labels);
        // 设置图表颜色
        $this->chartColors($colors);
        // 设置图的大小
        $this->chartHeight(150);
        // 设置图的格式
        $this->chart->style('margin: 15px 15px 0 0;width: 200px;float:right;');
        // 设置卡片高度
        $this->height(285);
    }

    protected function defaultChartOptions()
    {
        $color = Admin::color();

        $colors = [$color->primary(), $color->alpha('blue2', 0.5), $color->orange2()];

        return [
            'chart' => [
                'type' => 'donut',
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'colors' => $colors,
            'legend' => [
                'show' => false,
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'width' => 0,
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '75%',
                    ],
                ],
            ],
        ];
    }

    /**
     * 写入数据.
     *
     * @return void
     */
    public function handle(Request $request)
    {
        $curOption = $request->get('option')?:'30';
        $curTime = now();
        $curTimeBefor = now()->subDays($curOption)->toDateTimeString();

        $data = array();
        foreach (Status::select('id')->where('parent', 0)->get()->toarray() as $id) {
            $data[] = Pbind::whereHas('statuses', function (Builder $query) use ($id) { $query->where('parent', $id)->orWhere('id', $id); })
            ->whereBetween('created_at', [$curTimeBefor,$curTime])
            ->count();
        }
        // 不患寡而患不均
        $data_fake = $data;
        foreach ($data as $key => $value) {
            if($value) {
                if (max($data) / $value > 40) { $data_fake[$key] = max($data) / 40; }
            }
        }

        $color = Admin::color();
        $colors = [$color->red(),$color->yellow(), $color->green(),$color->blue(),$color->gray()];
 
        // 表
        $this->withContent($data, $colors);
        // 图
        $this->withChart($data_fake);
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
        return $this->chart([
            'series' => $data,
            'tooltip' => [
                'enabled' => true,
                'custom' => JavaScript::make(
                    <<<JS
                        function({seriesIndex, w}) {
                            return '<div class="apexcharts-tooltip-series-group apexcharts-active apexcharts-tooltip-text" style="display: flex; background-color: '
                            + w.config.colors[seriesIndex]
                            + '; font-family: Helvetica, Arial, sans-serif; font-size: 12px;"><span>' 
                            + w.config.labels[seriesIndex] 
                            + '</span></div>';
                        }
                    JS
                ),
            ]
        ]);
    }

    /**
     * 设置卡片头部内容.
     *
     * @param mixed $desktop
     * @param mixed $mobile
     *
     * @return $this
     */
    protected function withContent(array $data, $colors)
    {
        // $content = parent::render();


        $style = 'margin-bottom: 8px';
        $labelWidth = 120;

        return $this->content(
            <<<HTML
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[0]"></i> {$this->labels[0]}
    </div>
    <div>{$data[0]}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[1]"></i> {$this->labels[1]}
    </div>
    <div>{$data[1]}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[2]"></i> {$this->labels[2]}
    </div>
    <div>{$data[2]}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[3]"></i> {$this->labels[3]}
    </div>
    <div>{$data[3]}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[4]"></i> {$this->labels[4]}
    </div>
    <div>{$data[4]}</div>
</div>
HTML
        );
    }
}
