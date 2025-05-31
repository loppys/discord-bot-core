<?php

namespace Discord\Bot\Components\Settings\Events;

use Discord\Bot\Components\Settings\Repositories\SettingsRepository;
use Discord\Bot\System\Events\AbstractEventListener;
use Discord\Bot\System\GlobalRepository\Entities\SettingsLogEntity;
use Discord\Bot\System\GlobalRepository\SettingsLogRepository;
use Vengine\Libraries\Console\ConsoleLogger;
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
        $this->lastLogId = null;

        $setting = $this->settingsRepository->createEntity([
            'stg_guild' => $guild,
            'stg_name' => $name
        ]);

        if ($setting === null) {
            return;
        }

        $entity = $this->settingsLogRepository->newEntity();

        $res = $setting->toArray() ?? [];

        $entity->stl_before = serialize($res);

        if ($this->settingsLogRepository->saveByEntity($entity)) {
            $this->lastLogId = (int)$this->settingsLogRepository->getLastInsertId();
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

        if ($setting === null) {
            return;
        }

        $entity = $this->settingsLogRepository->createEntity([
            'stl_id' => $this->lastLogId
        ]);

        $this->lastLogId = null;

        if ($entity !== null) {
            $res = $setting->toArray() ?? [];

            $entity->stl_after = serialize($res);
            $this->settingsLogRepository->updateByEntity($entity, ['stl_after']);
        }
    }
}
