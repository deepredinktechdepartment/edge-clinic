<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDoctors extends Command
{
    protected $signature = 'doctors:sync';
    protected $description = 'Sync doctors from MocDoc API to local DB';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Call your existing syncDoctors logic
        $controller = new \App\Http\Controllers\MocDocController();
        $result = $controller->syncDoctors();

        $this->info('Doctors sync completed!');
    }
}
