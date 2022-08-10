<?php

namespace App\Console\Commands;

use App\Admin\Job\TimeAVG as JobTimeAVG;
use Illuminate\Console\Command;

class TimeAVG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timeavg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * 创建命令.
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
        $cur_command = new JobTimeAVG;
        $cur_command();
    }
}
