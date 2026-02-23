<?php

declare(strict_types=1);

namespace UseKadee\LaravelKadee;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Throwable;

class KadeeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/kadee.php', 'kadee');

        $this->app->singleton(ExceptionSerializer::class);
        $this->app->singleton(KadeeSender::class, function (Application $app) {
            $config = $app['config']['kadee'];
            
            return new KadeeSender(
                $config['endpoint'],
                $config['project'],
                $config['key'],
                $config['timeout']
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/kadee.php' => config_path('kadee.php'),
        ], 'kadee-config');

        $this->commands([
            TestCommand::class,
        ]);

        $this->registerExceptionHandler();
    }

    /**
     * Register the exception handler with Laravel's error reporting.
     */
    private function registerExceptionHandler(): void
    {
        if (!$this->shouldReport()) {
            return;
        }

        $this->app->singleton(KadeeExceptionReporter::class, function (Application $app) {
            return new KadeeExceptionReporter(
                $app->make(ExceptionSerializer::class),
                $app->make(KadeeSender::class)
            );
        });

        // Register the exception reporter
        $this->app->afterResolving('exception', function ($handler) {
            $handler->reportable(function (Throwable $e) {
                $this->app->make(KadeeExceptionReporter::class)->report($e);
            });
        });
    }

    /**
     * Determine if exceptions should be reported to Kadee.
     */
    private function shouldReport(): bool
    {
        $config = $this->app['config']['kadee'];
        
        if (empty($config['project']) || empty($config['key'])) {
            return false;
        }

        $currentEnv = $this->app->environment();
        $allowedEnvs = $config['environments'] ?? [];

        return in_array($currentEnv, $allowedEnvs, true);
    }
}