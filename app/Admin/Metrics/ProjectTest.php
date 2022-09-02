<?php

namespace App\Admin\Metrics;

use App\Admin\Rewrites\Donut;
use App\Models\Pbind;
use App\Models\PRequest;
use Dcat\Admin\Admin;
use Dcat\Admin\Support\JavaScript;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProjectTest extends Donut
{
    protected $labels = ['已提交','处理中','已处理','已解决','未解决','重新处理中','无法处理','已拒绝'];

    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $color = Admin::color();
        $colors = [$color->red(),$color->yellow(), $color->green(),$color->blue(),$color->gray()];

        $this->title('外设新增适配数据');
        
        $this->chartLabels($this->labels);
        // 设置图表颜色
        $this->chartColors($colors);

        $this->chartHeight(200);

        $this->chart->style('margin: 15px 15px 0 0;width: 300px;float:right;');
        $this->height(285);
    }

    /**
     * 写入数据.
     *
     * @return void
     */
    public function handle(Request $request)
    {
        $curOption = $request->get('option')?:0;
        if($curOption){
            $curProjectName = PRequest::where('project_name','like',"%{$curOption}%")->pluck('project_name')->first();

            $a1 = PRequest::where([['project_name',$curProjectName],['status','已提交']])->count();
            $a2 = PRequest::where([['project_name',$curProjectName],['status','处理中']])->count();
            $a3 = PRequest::where([['project_name',$curProjectName],['status','已处理']])->count();
            $a4 = PRequest::where([['project_name',$curProjectName],['status','已解决']])->count();
            $a5 = PRequest::where([['project_name',$curProjectName],['status','未解决']])->count();
            $a6 = PRequest::where([['project_name',$curProjectName],['status','重新处理中']])->count();
            $a7 = PRequest::where([['project_name',$curProjectName],['status','无法处理']])->count();
            $a8 = PRequest::where([['project_name',$curProjectName],['status','已拒绝']])->count();

        }else{
            $a1 = PRequest::where('status','已提交')->count();
            $a2 = PRequest::where('status','处理中')->count();
            $a3 = PRequest::where('status','已处理')->count();
            $a4 = PRequest::where('status','已解决')->count();
            $a5 = PRequest::where('status','未解决')->count();
            $a6 = PRequest::where('status','重新处理中')->count();
            $a7 = PRequest::where('status','无法处理')->count();
            $a8 = PRequest::where('status','已拒绝')->count();
        }
        
        $color = Admin::color();
        $colors = [$color->red(),$color->yellow(), $color->green(),$color->blue(),$color->gray()];
 

        $this->withContent($a1,$a2,$a3,$a4,$a5,$a6,$a7,$a8,$colors);

        // 图表数据

        $this->withChart([$a1,$a2,$a3,$a4,$a5,$a6,$a7,$a8]);

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
    protected function withContent($a1,$a2,$a3,$a4,$a5,$a6,$a7,$a8,$colors)
    {
        // $content = parent::render();

        $style = 'margin-bottom: 1px';
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
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[4]"></i> {$this->labels[5]}
    </div>
    <div>{$a6}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[4]"></i> {$this->labels[6]}
    </div>
    <div>{$a7}</div>
</div>
<div class="d-flex pl-1 pr-1 pt-1" style="{$style}">
    <div style="width: {$labelWidth}px">
        <i class="fa fa-circle" style="color: $colors[4]"></i> {$this->labels[7]}
    </div>
    <div>{$a8}</div>
</div>


HTML
        );
    }
}
