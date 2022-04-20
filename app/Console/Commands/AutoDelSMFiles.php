<?php

namespace App\Console\Commands;

use App\Models\SolutionMatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AutoDelSMFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AutoDelSMFiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每周删除创建7天以上的适配方案匹配文件';

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
        $curDataBefore = now()->subDays(7)->toDateString();

        $curSolutionMatch = SolutionMatch::whereDate('created_at', '<=' ,$curDataBefore);
        $curSolutionMatch->delete();

        $curFileArr = $curSolutionMatch->pluck('title')->toArray();
        foreach($curFileArr as $curFile)
        {
            Storage::disk('public')->delete('solution-match/'.$curFile);
        }
    }
}
