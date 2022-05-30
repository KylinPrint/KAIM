<?php

namespace App\Admin\Renderable;

use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Table;

use function PHPUnit\Framework\isEmpty;

class SolutionTable extends LazyRenderable
{
    public function render()
    {
        // 获取ID
        $i = 0;

        $solution = preg_split('/[;]+/',$this->StrReplace($this->solution));
        $solution_name = preg_split('/[;]+/',$this->StrReplace($this->solution_name));

        foreach($solution as $k=>$v){
            
            if(isEmpty($solution_name[$i])){
                $data[$i]['solution_name'] = $solution_name[$i];
            }else{
                $data[$i]['solution_name'] = '';
            }
            $data[$i]['solution'] = $v;
            
            $i++;
        }

        $titles = [
            '安装包名',
            '适配方案',
        ];
        $a = Table::make($titles, $data);
        return "<div style='padding:10px 400px 0 900px;text-align:center;line-height:40px'>$a</div>";
    }

    public function StrReplace(string $str){
        $arr = array('；' => ';');
        return strtr($str,$arr);
    }
}
