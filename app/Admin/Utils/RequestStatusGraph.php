<?php 

namespace App\Admin\Utils;

use Fhaculty\Graph\Graph;

class RequestStatusGraph
{
    public static function make()
    {
        $graph = new Graph;

        $submitted   = $graph->createVertex('已提交');
        $processing  = $graph->createVertex('处理中');
        $processed   = $graph->createVertex('已处理');
        $test_passed = $graph->createVertex('验证通过');
        $test_failed = $graph->createVertex('验证未通过');
        $test_reopen = $graph->createVertex('重新处理中');
        $helpless    = $graph->createVertex('无法处理');
        $denied      = $graph->createVertex('已拒绝');

        $submitted->createEdgeTo($processing);
        $submitted->createEdgeTo($denied);

        $processing->createEdgeTo($processed);

        $processed->createEdgeTo($test_passed);
        $processed->createEdgeTo($test_failed);

        $test_failed->createEdgeTo($test_reopen);
        $test_failed->createEdgeTo($helpless);

        $helpless->createEdgeTo($test_reopen);

        $test_reopen->createEdgeTo($processed);

        return $graph;
    }
}