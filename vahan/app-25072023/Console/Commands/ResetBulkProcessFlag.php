<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class ResetBulkProcessFlag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:restbulk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Cron For Reset the Bulk Proces';

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
        $controller = new CronController();
        $controller->resetBulkProcessFlag();
    }
}
