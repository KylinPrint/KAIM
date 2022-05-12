<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lizhichao\Word\VicWord;

class DictExec extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dict:exec {word}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '分词';

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
        $fc = new VicWord(resource_path('dict') . '/brand.igb');
        foreach ($fc->getWord($this->argument('word')) as $key => $value) {
            $this->line($value[0] . ' ' . $value[3]);
            
        }
        return 0;
    }
}
