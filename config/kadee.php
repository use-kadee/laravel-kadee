<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Project UUID
    |--------------------------------------------------------------------------
    |
    | Your Kadee project UUID. This is used as part of the ingest API endpoint.
    |
    */
    'project' => env('KADEE_PROJECT'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret Key
    |--------------------------------------------------------------------------
    |
    | Your Kadee webhook secret key. This is used for HMAC-SHA256 signature
    | authentication when sending data to the ingest API.
    |
    */
    'key' => env('KADEE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Kadee Endpoint
    |--------------------------------------------------------------------------
    |
    | The Kadee ingest API endpoint. You probably don't need to change this.
    |
    */
    'endpoint' => env('KADEE_ENDPOINT', 'https://usekadee.com/api/ingest'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for HTTP requests to the Kadee API.
    |
    */
    'timeout' => (int) env('KADEE_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    |
    | The environments where exceptions should be sent to Kadee. Exceptions
    | will only be reported when the current environment is in this array.
    |
    */
    'environments' => ['production', 'staging'],
];