<?php

namespace AlkhatibDev\LogRotation\Console\Commands;

use AlkhatibDev\LogRotation\LogRotator;
use Illuminate\Console\Command;

class RotateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logrotation:rotate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate log files using logrotation package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        LogRotator::make()->rotate();
    }
}
