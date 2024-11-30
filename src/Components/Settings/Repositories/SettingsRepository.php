<?php

namespace Discord\Bot\Components\Settings\Repositories;

use Discord\Bot\Components\Settings\Entity\Setting;
use Discord\Bot\System\Repository\AbstractRepository;
use Discord\Bot\System\Repository\Entity\AbstractEntity;

/**
 * @method Setting|null createEntity(array $criteria = [])
 * @method Setting|null createEntityByArray(array $data)
 * @method Setting|null newEntity()
 */
class SettingsRepository extends AbstractRepository
{
    protected string $table = 'settings';

    protected string $primaryKey = 'stg_id';

    protected array $columnMap = [
        'stg_guild',
        'stg_name',
        'stg_value',
        'stg_type',
        'stg_enabled',
        'stg_required',
        'stg_system',
        'stg_hidden'
    ];

    protected string $entityClass = Setting::class;
}
