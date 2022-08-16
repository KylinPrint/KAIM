<?php

namespace App\Admin\Metrics;

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

class PRequestTime extends Bar
{
    protected $labels = ['接收耗时','解决耗时','无法处理耗时'];
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
        $this->title('需求平均耗时');
        $this->dropdown([
            '7' => 'Last 7 Days',
            '30' => 'Last Month',
            '365' => 'Last Year',
            '1' => 'All'
        ]);

        // 设置图表颜色
        $this->chartColors([
            $dark35,
            $color->primary(),
            $dark35,
        ]);
    }

    /**
     * 处理请求
     *
     * @param Request $request
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        $o = [
            'processing_sum'    => 0 ,'processing_count'   => 0,
            'processed_sum'     => 0 ,'processed_count'    => 0,
            'fail_process_sum'  => 0 ,'fail_process_count' => 0,
        ];
        $cache_name = 'p_request_time_avg_'.$request->get('option');
        $a = Cache::get($cache_name)?:$o;

        $color = Admin::color();
        $colors = [$color->yellow(), $color->green(),$color->blue(),$color->gray()];
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
        $label = strtolower(
            $this->dropdown[request()->option] ?? 'last 7 days'
        );


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
HTML
        );
    }
}
