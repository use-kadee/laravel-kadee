<?php

declare(strict_types=1);

namespace UseKadee\LaravelKadee;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'kadee:test';
    
    protected $description = 'Test Kadee error reporting by throwing a test exception';

    public function handle(KadeeExceptionReporter $reporter): int
    {
        $this->info('Throwing a test exception...');

        $exception = new KadeeTestException('This is a test exception from the Kadee Laravel package');

        try {
            $reporter->report($exception);
        } catch (\Throwable $e) {
            $this->error('Failed to send test exception to Kadee: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Test exception sent successfully!');
        $this->info('Check your Kadee dashboard to see if the exception was reported.');

        return self::SUCCESS;
    }
}