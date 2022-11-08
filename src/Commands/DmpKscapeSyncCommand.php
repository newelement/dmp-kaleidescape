<?php

namespace Newelement\DmpKscape\Commands;

use Illuminate\Console\Command;
use Newelement\DmpKscape\Services\KscapeMediaSyncService;

class DmpKscapeSyncCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'dmp-kscape:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DMP Kscape poster sync';


    public function handle()
    {
        $service = new KscapeMediaSyncService();
        $service->syncMedia();
    }
}
