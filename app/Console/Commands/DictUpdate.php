<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lizhichao\Word\VicDict;

class DictUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dict:update {dict}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update VicWord dictionary';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //目前可支持 igb 和 json 两种词典库格式；igb需要安装igbinary扩展，igb文件小，加载快
        $path = resource_path('dict') . '/' . $this->argument('dict') . '.igb'; //词典地址
        $dict = new VicDict($path);

        $a = ["立象", "震旦", "标拓", "兄弟", "佳能", "恒银金融", "中航", "沧田", "得实", "得力", "东纳", "宜联", "爱普生", "FARGO", "方正", "富士胶片", "富士通", "佳博", "科诚", "长城", "固德卡", "汉光", "惠普", "汉印", "爱胜品", "奇普", "柯尼卡美能达", "京瓷", "立思辰", "雷丹", "联想", "利盟", "Magicard", "美松", "南天", "日冲", "Olicom", "奔图", "理光", "理想", "佐藤", "安普印", "夏普", "实诺锐", "STAR", "星谷", "实达", "晟拓", "光电通", "东芝", "TSC", "致明兴", "中盈", "德佟", "裕佳", "中矗", "航天双翼", "ARGOX", "AURORA", "Biaotop", "Brother", "Canon", "CASHWAY", "CITIC", "CUMTENN", "Dascom", "Deli", "Donna", "Elean", "Epson", "Founder", "FUJIFILM", "Fujitsu", "Gainscha", "GODEX", "GreatWall", "gudecard", "HGOA", "HP", "HPRT", "ICSP", "KIP", "KONICA MINOLTA", "KYOCERA", "LANXUM", "LEDEN", "Lenovo", "Lexmark", "MASUNG", "Nantian", "OKI", "Pantum", "Ricoh", "RISO", "SATO", "SecuPrint", "SHARP", "SONARAY", "Starmach", "Start", "suntalk", "TOEC", "TOSHIBA", "ZMiN", "zonewin"];
        foreach ($a as $b) {
            //添加词语词库 add(词语,词性) 不分语言，可以是utf-8编码的任何字符
            $dict->add($b, 'n');
        }


        //保存词库
        $dict->save();
        return 0;
    }
}
