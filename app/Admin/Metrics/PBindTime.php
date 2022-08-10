<?php

namespace App\Admin\Metrics;

use App\Admin\Job\TimeAVG;
use App\Models\Pbind;
use App\Models\PRequest;
use App\Models\Sbind;
use App\Models\Status;
use Carbon\Carbon;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Bar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Models\Audit;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Averages;

class PBindTime extends Bar
{
    protected $labels = ['适配复测排期','适配复测','系统问题处理','上架','证书制作及归档'];
    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $color = Admin::color();

        $dark35 = $color->dark35();

        // $this->chartLabels($this->labels);

        // 卡片内容宽度
        $this->contentWidth(5, 7);
        // 标题
        $this->title('适配平均耗时');
        // 设置下拉选项

        // 设置图表颜色
        $this->chartColors([
            $dark35,
            $dark35,
            $color->primary(),
            $dark35,
            $dark35,
            $dark35
        ]);
    }

    /**
     * 处理请求
     *
     * @param Request $request
     *
     * @return mixed|void
     */
    public function handle()
    {
        $o = [
            'status_1_sum'    => 0 ,'status_1_count'   => 0,
            'status_2_sum'    => 0 ,'status_2_count'   => 0,
            'status_3_sum'    => 0 ,'status_3_count'   => 0,
            'status_4_sum'    => 0 ,'status_4_count'   => 0,
            'status_5_sum'    => 0 ,'status_5_count'   => 0,
        ];
        $a = Cache::get('p_bind_time_avg')?:$o;
        // $b = Cache::get('p_request_time_avg');
        $color = Admin::color();
        $colors = [$color->red(),$color->yellow(), $color->green(),$color->blue(),$color->gray()];
        // 卡片内容
        $this->withContent(array_values($a), $colors);

        // 图表数据
        $this->withChart([
            [
                'data' => array_values($a),
            ],
        ]); 
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
        ]);
    }

    /**
     * 设置卡片内容.
     *
     * @param string $title
     * @param string $value
     * @param string $style
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
    <div>{$data[0]} h</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[1]"></i> {$this->labels[1]}
    </div>
    <div>{$data[1]} h</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[2]"></i> {$this->labels[2]}
    </div>
    <div>{$data[2]} h</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[3]"></i> {$this->labels[3]}
    </div>
    <div>{$data[3]} h</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[4]"></i> {$this->labels[4]}
    </div>
    <div>{$data[4]} h</div>
</div>
HTML
        );
    }
}
