<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheClean extends Command
{
    protected $signature = 'cache:clean';
    protected $description = 'Clean all cached data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->call('optimize');
        $this->call('route:clear');
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('view:clear');
        $this->call('config:cache');

        return Command::SUCCESS;
    }
}
