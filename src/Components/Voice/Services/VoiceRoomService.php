<?php

namespace Discord\Bot\Components\Voice\Services;

use Discord\Bot\Components\Voice\Repository\VoiceRoomRepository;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Voice\VoiceClient;
use React\Stream\ReadableResourceStream;

class VoiceRoomService
{
    protected Discord $discord;

    protected VoiceRoomRepository $repository;

    public function __construct(VoiceRoomRepository $repository)
    {
        $this->repository = $repository;
    }

    public function initDiscord(Discord $discord): static
    {
        $this->discord = $discord;

        return $this;
    }

    public function playSound(Channel $channel, string $song): bool
    {
        if ($channel->guild_id === null) {
            return false;
        }

        $client = $this->getVoiceClient($channel->guild_id);

        if ($client === null) {
            $this->discord->joinVoiceChannel($channel)->done(function () use ($channel, $song) {
                $client = $this->getVoiceClient($channel->guild_id);

                if ($client === null) {
                    return false;
                }

                $this->play($client, $song);

                return true;
            });
        } else {
            $this->play($client, $song);
        }

        return true;
    }

    protected function play(VoiceClient $client, string $song): void
    {
        if (!$client->isReady() && !$client->start()) {
            return;
        }

        if (!$this->isUrl($song)) {
            $fileStream = fopen("https://vengine.ru/upload/{$song}.mp3", 'r');

        } else {
            $fileStream = fopen($song, 'r');
        }

        $res = new ReadableResourceStream($fileStream);
        $client->playRawStream($res)->done(function () use ($res) {
            $res->close();
        });
    }

    protected function getVoiceClient(string $guildId): ?VoiceClient
    {
        return $this->discord->getVoiceClient($guildId);
    }

    private function isUrl(string $string): bool
    {
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }
}
