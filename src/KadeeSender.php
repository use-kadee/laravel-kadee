<?php

declare(strict_types=1);

namespace UseKadee\LaravelKadee;

class KadeeSender
{
    public function __construct(
        private string $endpoint,
        private string $project,
        private string $key,
        private int $timeout = 5
    ) {
    }

    /**
     * Send exception data to Kadee's ingest API.
     */
    public function send(array $payload): bool
    {
        try {
            $jsonBody = json_encode($payload, JSON_THROW_ON_ERROR);
            $signature = hash_hmac('sha256', $jsonBody, $this->key);
            $url = rtrim($this->endpoint, '/') . '/' . $this->project;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonBody,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Signature: ' . $signature,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => $this->timeout,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'use-kadee/laravel-kadee',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 202;
        } catch (\Exception) {
            // Fail silently - never throw exceptions
            return false;
        }
    }
}