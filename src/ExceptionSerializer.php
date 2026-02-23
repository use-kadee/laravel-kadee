<?php

declare(strict_types=1);

namespace UseKadee\LaravelKadee;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class ExceptionSerializer
{
    /**
     * Serialize a throwable and request into Kadee's expected format.
     */
    public function serialize(Throwable $throwable, ?Request $request = null): array
    {
        return [
            'exceptionClass' => get_class($throwable),
            'message' => $throwable->getMessage(),
            'seenAtUnixNano' => $this->getCurrentTimestampInNanoseconds(),
            'stacktrace' => $this->buildStackTrace($throwable),
            'attributes' => $this->buildAttributes($request),
            'events' => [],
            'trackingUuid' => Str::uuid()->toString(),
        ];
    }

    /**
     * Get the current timestamp in nanoseconds.
     */
    private function getCurrentTimestampInNanoseconds(): int
    {
        return (int) (microtime(true) * 1_000_000_000);
    }

    /**
     * Build the stacktrace array from the throwable.
     */
    private function buildStackTrace(Throwable $throwable): array
    {
        $stackTrace = [];
        $basePath = base_path();
        $vendorPath = base_path('vendor');

        // Add the initial exception frame
        if ($throwable->getFile()) {
            $stackTrace[] = $this->buildFrame(
                $throwable->getFile(),
                $throwable->getLine(),
                null,
                null,
                $basePath,
                $vendorPath
            );
        }

        // Add all trace frames
        foreach ($throwable->getTrace() as $frame) {
            $stackTrace[] = $this->buildFrame(
                $frame['file'] ?? null,
                $frame['line'] ?? 0,
                $frame['function'] ?? null,
                $frame['class'] ?? null,
                $basePath,
                $vendorPath,
                $frame['args'] ?? []
            );
        }

        return $stackTrace;
    }

    /**
     * Build a single stack frame.
     */
    private function buildFrame(
        ?string $file,
        int $line,
        ?string $method,
        ?string $class,
        string $basePath,
        string $vendorPath,
        array $arguments = []
    ): array {
        return [
            'file' => $file,
            'lineNumber' => $line,
            'method' => $method,
            'class' => $class,
            'codeSnippet' => $this->getCodeSnippet($file, $line),
            'arguments' => $this->serializeArguments($arguments),
            'isApplicationFrame' => $this->isApplicationFrame($file, $basePath, $vendorPath),
        ];
    }

    /**
     * Get code snippet around the error line.
     */
    private function getCodeSnippet(?string $file, int $line): array
    {
        if (!$file || !is_readable($file)) {
            return [];
        }

        try {
            $lines = file($file, FILE_IGNORE_NEW_LINES);
            if ($lines === false) {
                return [];
            }

            $start = max(0, $line - 6);
            $end = min(count($lines) - 1, $line + 4);
            $snippet = [];

            for ($i = $start; $i <= $end; $i++) {
                $snippet[(string) ($i + 1)] = $lines[$i] ?? '';
            }

            return $snippet;
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Serialize function arguments.
     */
    private function serializeArguments(array $arguments): array
    {
        $serialized = [];

        foreach ($arguments as $arg) {
            if (is_object($arg)) {
                $serialized[] = get_class($arg);
            } elseif (is_array($arg)) {
                $serialized[] = 'array(' . count($arg) . ')';
            } elseif (is_string($arg)) {
                $serialized[] = '"' . (strlen($arg) > 50 ? substr($arg, 0, 50) . '...' : $arg) . '"';
            } else {
                $serialized[] = gettype($arg) . '(' . var_export($arg, true) . ')';
            }
        }

        return $serialized;
    }

    /**
     * Determine if a file is an application frame (not vendor code).
     */
    private function isApplicationFrame(?string $file, string $basePath, string $vendorPath): bool
    {
        if (!$file) {
            return false;
        }

        return str_starts_with($file, $basePath) && !str_starts_with($file, $vendorPath);
    }

    /**
     * Build attributes array with request and system information.
     */
    private function buildAttributes(?Request $request = null): array
    {
        $attributes = [
            'laravel.version' => app()->version(),
            'php.version' => PHP_VERSION,
            'environment' => app()->environment(),
            'server.hostname' => gethostname() ?: 'unknown',
        ];

        if ($request) {
            $attributes['url.full'] = $request->fullUrl();
            $attributes['http.request.method'] = $request->method();
        }

        return $attributes;
    }
}