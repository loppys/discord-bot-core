<?php

namespace Discord\Bot\Components\Settings\Repositories;

use Discord\Bot\Components\Settings\Entity\Setting;
use Discord\Bot\System\Repository\AbstractRepository;

/**
 * @method Setting|null createEntity(array $criteria = [])
 * @method Setting|null createEntityByArray(array $data)
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
        'stg_system'
    ];

    protected string $entityClass = Setting::class;
}
