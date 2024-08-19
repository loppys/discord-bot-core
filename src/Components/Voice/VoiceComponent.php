<?php

namespace Discord\Bot\Components\Voice;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Voice\Repository\VoiceQueueRepository;
use Discord\Bot\Components\Voice\Repository\VoiceRoomRepository;
use Discord\Bot\Components\Voice\Services\VoiceRoomService;
use Discord\Bot\Components\Voice\Services\VoiceQueueService;
use Discord\Bot\Core;
use Discord\Discord;
use Discord\Parts\Channel\Channel;

class VoiceComponent extends AbstractComponent
{
    protected VoiceQueueService $voiceQueueService;

    protected VoiceQueueRepository $voiceQueueRepository;

    public function __construct(
        VoiceRoomService $service,
        VoiceRoomRepository $repository,
        VoiceQueueRepository $voiceQueueRepository,
        VoiceQueueService $voiceQueueService
    ) {
        parent::__construct($service);

        $this->voiceQueueRepository = $voiceQueueRepository;
        $this->voiceQueueService = $voiceQueueService;
    }

    public function playSong(Channel $channel, string $song): bool
    {
        return $this->getService()->initDiscord($this->discord)->playSound($channel, $song);
    }

    public function getService(): VoiceRoomService
    {
        return $this->service;
    }
}