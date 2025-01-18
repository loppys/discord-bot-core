<?php

namespace Discord\Bot\System\Remote\Clients;

use Discord\Bot\System\Remote\AbstractClient;
use Discord\Bot\System\Remote\Interfaces\HttpClientInterface;
use Vengine\Libraries\Console\ConsoleLogger;
use GuzzleHttp\Client as GuzzleClient;

class HttpClient extends AbstractClient implements HttpClientInterface
{
    private GuzzleClient $client;

    public function __construct()
    {
        $this->client = new GuzzleClient();
    }

    public function connect(): bool
    {
        $this->isConnected = true;

        return $this->isConnected;
    }

    public function execute(string $command): mixed
    {
        ConsoleLogger::showMessage('HTTP client does not support generic commands.');
        trigger_error('HTTP client does not support generic commands.');

        return null;
    }

    public function get(string $url, array $headers = []): string
    {
        $response = $this->client->get($url, ['headers' => $headers]);

        return $response->getBody()->getContents();
    }

    public function post(string $url, array $data, array $headers = []): string
    {
        $response = $this->client->post($url, [
            'headers' => $headers,
            'form_params' => $data,
        ]);

        return $response->getBody()->getContents();
    }
}
