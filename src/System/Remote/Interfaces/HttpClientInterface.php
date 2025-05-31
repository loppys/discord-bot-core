<?php

namespace Discord\Bot\System\Remote\Interfaces;

interface HttpClientInterface extends ClientInterface
{
    public function get(string $url, array $headers = []): string;
    public function post(string $url, array $data, array $headers = []): string;
}
