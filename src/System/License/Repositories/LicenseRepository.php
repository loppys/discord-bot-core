<?php

namespace Discord\Bot\System\License\Repositories;

use Vengine\Libraries\DBAL\Adapter;
use Discord\Bot\System\License\Entities\LicenseEntity;
use Vengine\Libraries\Repository\AbstractRepository;
use Vengine\Libraries\Repository\CriteriaComparator;
use Vengine\Libraries\Repository\Entity\AbstractEntity;

/**
 * @method LicenseEntity|null createEntity(array $criteria = [])
 * @method LicenseEntity|null createEntityByArray(array $data)
 * @method LicenseEntity|null newEntity()
 * @method LicenseEntity[] getAll(array $criteria = [])
 */
class LicenseRepository extends AbstractRepository
{
    protected string $table = 'license';

    protected string $primaryKey = 'lns_id';

    protected array $columnMap = [
        'lns_guild',
        'lns_universe',
        'lns_key',
        'lns_expired',
        'lns_infinity',
        'lns_master',
        'lns_trial',
        'lns_time_end',
        'lns_time_activate',
        'lns_component_class',
        'lns_component_name',
        'lns_use_component_class',
    ];

    protected string $entityClass = LicenseEntity::class;
}
