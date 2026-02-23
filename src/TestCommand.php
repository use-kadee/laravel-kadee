<?php

declare(strict_types=1);

namespace UseKadee\LaravelKadee;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'kadee:test';
    
    protected $description = 'Test Kadee error reporting by throwing a test exception';

    public function handle(): int
    {
        $this->info('Throwing a test exception...');

        try {
            throw new KadeeTestException('This is a test exception from the Kadee Laravel package');
        } catch (KadeeTestException $e) {
            // The exception will be caught by the error handler and sent to Kadee
            $this->info('Test exception thrown successfully!');
            $this->info('Check your Kadee dashboard to see if the exception was reported.');
            return self::SUCCESS;
        }
    }
}