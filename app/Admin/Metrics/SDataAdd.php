<?php

namespace App\Admin\Metrics;

use App\Models\Pbind;
use App\Models\Sbind;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Donut;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SDataAdd extends Donut
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

        $this->title('软件新增适配数据');
        $this->dropdown([
            '30' => '30天内',
            '7' => '7天内',
            '365' => '1年内',
        ]);
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
        $this->height(285);
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
        $curOption = $request->get('option')?:'30';
        $curTime = now();
        $curTimeBefor = now()->subDays($curOption)->toDateTimeString();

        // $AddNum = count(Pbind::all()->whereBetween('created_at',[$curTimeBefor,$curTime]));
        $a1 = 
            Sbind::whereHas('statuses', function (Builder $query){
                $query->where('parent','1')->orWhere('id','1');
            })->whereBetween('created_at',[$curTimeBefor,$curTime])->count();

        $a2 = 
            Sbind::whereHas('statuses', function (Builder $query){
                $query->where('parent','2')->orWhere('id','2');
            })->whereBetween('created_at',[$curTimeBefor,$curTime])->count();

        $a3 = 
            Sbind::whereHas('statuses', function (Builder $query){
                $query->where('parent','3')->orWhere('id','3');
            })->whereBetween('created_at',[$curTimeBefor,$curTime])->count();

        $a4 = 
            Sbind::whereHas('statuses', function (Builder $query){
                $query->where('parent','4')->orWhere('id','4');
            })->whereBetween('created_at',[$curTimeBefor,$curTime])->count();

        $a5 = 
            Sbind::whereHas('statuses', function (Builder $query){
                $query->where('parent','5')->orWhere('id','5');
            })->whereBetween('created_at',[$curTimeBefor,$curTime])->count();
        
        $color = Admin::color();
        $colors = [$color->red(),$color->yellow(), $color->green(),$color->blue(),$color->gray()];
 

        $this->withContent($a1,$a2,$a3,$a4,$a5,$colors);

        // 图表数据

        $this->withChart([$a1,$a2,$a3,$a4,$a5]);

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
    protected function withContent($a1,$a2,$a3,$a4,$a5,$colors)
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
    <div>{$a1}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[1]"></i> {$this->labels[1]}
    </div>
    <div>{$a2}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[2]"></i> {$this->labels[2]}
    </div>
    <div>{$a3}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[3]"></i> {$this->labels[3]}
    </div>
    <div>{$a4}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[4]"></i> {$this->labels[4]}
    </div>
    <div>{$a5}</div>
</div>


HTML
        );
    }
}
