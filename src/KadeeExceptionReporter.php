<?php

declare(strict_types=1);

namespace UseKadee\LaravelKadee;

use Throwable;

class KadeeExceptionReporter
{
    public function __construct(
        private ExceptionSerializer $serializer,
        private KadeeSender $sender
    ) {
    }

    /**
     * Report an exception to Kadee.
     */
    public function report(Throwable $exception): void
    {
        try {
            $request = null;

            // Try to get the current request if available
            if (app()->bound('request')) {
                try {
                    $request = app('request');
                } catch (\Exception) {
                    // Ignore if request is not available
                }
            }

            $payload = $this->serializer->serialize($exception, $request);
            $this->sender->send($payload);
        } catch (\Exception) {
            // Fail silently - never interfere with the application
        }
    }
}