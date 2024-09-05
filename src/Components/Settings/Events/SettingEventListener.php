<?php

namespace Discord\Bot\Components\Settings\Events;

use Discord\Bot\Components\Settings\Repositories\SettingsRepository;
use Discord\Bot\System\Events\AbstractEventListener;
use Discord\Bot\System\GlobalRepository\Entities\SettingsLogEntity;
use Discord\Bot\System\GlobalRepository\SettingsLogRepository;
use Doctrine\DBAL\Exception;

class SettingEventListener extends AbstractEventListener
{
    protected SettingsLogRepository $settingsLogRepository;

    protected SettingsRepository $settingsRepository;

    private ?int $lastLogId = null;

    public function __construct(
        SettingsLogRepository $settingsLogRepository,
        SettingsRepository $settingsRepository
    ) {
        $this->settingsLogRepository = $settingsLogRepository;
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @throws Exception
     */
    public function beforeUpdateSetting(string $name, string $guild, array $data = []): void
    {
        $setting = $this->settingsRepository->createEntity([
            'stg_guild' => $guild,
            'stg_name' => $name
        ]);

        $entity = new SettingsLogEntity();
        $entity->stl_before = $setting?->toArray() ?? [];

        if (!$this->settingsLogRepository->saveByEntity($entity)) {
            $this->lastLogId = (int)$this->settingsRepository->getLastInsertId();
        }
    }

    /**
     * @throws Exception
     */
    public function afterUpdateSetting(string $name, string $guild, array $data = []): void
    {
        $setting = $this->settingsRepository->createEntity([
            'stg_guild' => $guild,
            'stg_name' => $name
        ]);

        $entity = $this->settingsLogRepository->createEntity([
            'stl_id' => $this->lastLogId
        ]);

        if ($entity !== null) {
            $this->lastLogId = null;

            $entity->stl_after = $setting?->toArray() ?? [];

            $this->settingsLogRepository->saveByEntity($entity);
        }
    }
}