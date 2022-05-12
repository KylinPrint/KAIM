<?php

namespace App\Admin\Metrics;

use App\Models\Peripheral;
use App\Models\Software;
use Dcat\Admin\Admin;
use Dcat\Admin\Support\JavaScript;
use Dcat\Admin\Widgets\Metrics\Line;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NewData extends Line
{
    protected $labels = ['外设','软件'];
    /**
     * 初始化卡片内容
     *
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->title('新增产品数据');
        $this->dropdown([
            '7' => '7天内',
            '30' => '30天内',
            '365' => '一年内'
        ]);
    }

    /**
     * 查询数据
     *
     * @param int $range
     *
     * @return array
     */
    public function P_query(int $range) {
        // 按时间从远到进
        for ($offset = $range - 1; $offset >= 0; $offset--) {
            $array[] = Peripheral::whereDate('created_at', today()->subDays($offset))->count();
        }
        $a = $array;
        return $array;
    }

    public function S_query(int $range) {
        // 按时间从远到进
        for ($offset = $range - 1; $offset >= 0; $offset--) {
            $array[] = Software::whereDate('created_at', today()->subDays($offset))->count();
        }
        $a = $array;
        return $array;
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
       
        // 默认7天
        $range = $request->get('option') ?? 7;
        // 查数据
        $p = $this->P_query($range);
        $s = $this->S_query($range);
        // 填充卡片内容
        // 数组求和
        $this->withContent(array_sum($p),array_sum($s));
        // 填充图表数据
        $this->withChart($p,$s);
        
    }

    /**
     * 设置图表数据.
     *
     * @param array $data
     *
     * @return $this
     */
    public function withChart(array $pdata,array $sdata)
    {
        return $this->chart([
            'series' => [
                [
                    'name' => '外设产品新增',
                    'data' => $pdata,    
                ],
                [
                    'name' => '软件产品新增',
                    'data' => $sdata, 
                ]
            ],
            'colors' => ['#77B6EA', '#545454'],
            'yaxis' => [[
                'labels' => [
                    'formatter' => JavaScript::make(
                    <<<JS
                        function(val) {
                            if (window.isNaN(val) || Math.floor(val) != val) {
                                return val;
                            }
                            try{
                                return val.toFixed(0);
                            } catch(e){
                                return val;
                            }
                        }
                    JS)
                ]
            ],],    
        ]);
    }

    /**
     * 设置卡片内容.
     *
     * @param string $content
     *
     * @return $this
     */
    public function withContent($p,$s)
    {
        $style = 'margin-bottom: 8px';
        $labelWidth = 75;

        return $this->content(
            <<<HTML
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div class="col-md-6" style="text-align:right">
        <i class="fa fa-circle" style="color: #77B6EA"></i>
        {$this->labels[0]}
        {$p}
    </div>
    <div class="col-md-6" style="text-align:left">
        <i class="fa fa-circle" style="color: #545454"></i>
        {$this->labels[1]}
        {$s}
    </div>
</div>
HTML
        );
    }
}
