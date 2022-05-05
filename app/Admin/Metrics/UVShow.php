<?php

namespace App\Admin\Metrics;

use Dcat\Admin\OperationLog\Models\OperationLog;
use Dcat\Admin\Widgets\Metrics\Line;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UVShow extends Line
{
    /**
     * 初始化卡片内容
     *
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->title('页面访问量');
        $this->dropdown([
            '7' => '7天内',
            '30' => '30天内',
        ]);
    }

    /**
     * 查询数据
     *
     * @param int $range
     *
     * @return array
     */
    public function query(int $range) {
        // 按时间从远到进
        for ($offset = $range - 1; $offset >= 0; $offset--) {
            $array[] = OperationLog::whereDate('created_at', today()->subDays($offset))->where('method', 'GET')->count();
        }
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
        // 检查是否安装operation-log插件
        if (!Schema::hasTable('admin_operation_log')) {
            $this->content('<p><h2 class="ml-1">请安装操作日志扩展</h2>');
        } else {
            // 默认7天
            $range = $request->get('option') ?? 7;
            // 查数据
            $query = $this->query($range);
            // 填充卡片内容
            $this->withContent($query[$range - 1]);
            // 填充图表数据
            $this->withChart($query);
        }
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
            'series' => [
                [
                    'name' => $this->title,
                    'data' => $data,
                ],
            ],
        ]);
    }

    /**
     * 设置卡片内容.
     *
     * @param string $content
     *
     * @return $this
     */
    public function withContent($content)
    {
        return $this->content(
            <<<HTML
<div class="d-flex justify-content-between align-items-center mt-1" style="margin-bottom: 2px">
    <h1 class="ml-1 font-lg-1">
        <span class="text-capitalize">{$content}</span>
        <span class="mb-0 mr-1 text-80"><small><small><small><small>今日</small></small></small></small></span>
    </h1>
</div>
HTML
        );
    }
}
