# Laravel Kadee

A Laravel error tracking package that sends exceptions directly to Kadee's ingest API.

## Installation

Install the package via Composer:

```bash
composer require use-kadee/laravel-kadee
```

The service provider will be automatically discovered and registered.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=kadee-config
```

Add your Kadee credentials to your `.env` file:

```env
KADEE_PROJECT=your-project-uuid
KADEE_KEY=your-webhook-secret
KADEE_ENDPOINT=https://usekadee.com/api/ingest
KADEE_TIMEOUT=5
```

## Configuration Options

The configuration file (`config/kadee.php`) contains the following options:

- `project` - Your Kadee project UUID (required)
- `key` - Your Kadee webhook secret key (required)  
- `endpoint` - The Kadee ingest API endpoint (default: `https://usekadee.com/api/ingest`)
- `timeout` - Request timeout in seconds (default: `5`)
- `environments` - Array of environments where exceptions should be reported (default: `['production', 'staging']`)

## Usage

Once configured, the package will automatically capture and send exceptions to Kadee when they occur in the configured environments.

### Testing

You can test the integration by running:

```bash
php artisan kadee:test
```

This will throw a test exception that should appear in your Kadee dashboard.

## How It Works

1. **Exception Capture**: The package registers with Laravel's exception handler to automatically capture exceptions
2. **Serialization**: Exceptions are serialized into Kadee's expected format with full stack traces, code snippets, and request context
3. **Authentication**: Requests are authenticated using HMAC-SHA256 signatures
4. **Error Handling**: The package fails silently and never interferes with your application's normal operation

## Payload Format

The package sends exception data in the following format:

```json
{
  "exceptionClass": "App\\Exceptions\\SomeException",
  "message": "Something went wrong", 
  "seenAtUnixNano": 1708700000000000000,
  "stacktrace": [
    {
      "file": "/app/Http/Controllers/FooController.php",
      "lineNumber": 42,
      "method": "index", 
      "class": "App\\Http\\Controllers\\FooController",
      "codeSnippet": {"40": "  $foo = bar();", "41": "  // ...", "42": "  throw new \\Exception('test');"},
      "arguments": [],
      "isApplicationFrame": true
    }
  ],
  "attributes": {
    "url.full": "https://example.com/foo",
    "http.request.method": "GET",
    "laravel.version": "10.48.0",
    "php.version": "8.2.0",
    "environment": "production",
    "server.hostname": "web-server"
  },
  "events": [],
  "trackingUuid": "550e8400-e29b-41d4-a716-446655440000"
}
```

## Requirements

- PHP 8.1+
- Laravel 10.0+

## License

MIT