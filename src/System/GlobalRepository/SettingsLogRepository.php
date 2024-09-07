<?php

namespace Discord\Bot\System\GlobalRepository;

use Discord\Bot\System\GlobalRepository\Entities\SettingsLogEntity;
use Discord\Bot\System\Repository\AbstractRepository;

/**
 * @method SettingsLogEntity|null createEntity(array $criteria = [])
 * @method SettingsLogEntity|null createEntityByArray(array $data)
 */
class SettingsLogRepository extends AbstractRepository
{
    protected string $table = 'settings_log';

    protected string $primaryKey = 'stl_id';

    protected array $columnMap = [
        'stl_before',
        'stl_after'
    ];

    protected string $entityClass = SettingsLogEntity::class;
}