<?php
// src/Service/LoggerService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoggerService
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function sendLog(array $data, string $apiUrl): void
    {
        try {
            $this->client->request('POST', $apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($data),
            ]);
        } catch (\Exception $e) {
            
        }
    }
}
