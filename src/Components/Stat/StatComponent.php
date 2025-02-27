<?php

namespace Discord\Bot\Components\Stat;

use Discord\Bot\Components\Stat\DTO\LevelCalculation;
use Discord\Bot\Components\Stat\DTO\StatQuery;
use Discord\Bot\Components\Stat\Entity\StatEntity;
use Discord\Bot\Components\Stat\Services\LevelCalculationService;
use Discord\Bot\Components\Stat\Services\StatService;
use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Stat\Storages\StatQueryTypeStorage;
use Discord\Bot\Scheduler\Storage\TaskTypeStorage;
use Doctrine\DBAL\Exception;

/**
 * @method StatService getService()
 * @property StatService $service
 */
class StatComponent extends AbstractComponent
{
    protected string $mainServiceClass = StatService::class;

    protected array $additionServices = [
        'levelCalculationService' => LevelCalculationService::class,
    ];

    protected LevelCalculationService $levelCalculationService;

    protected array $migrationList = [
        __DIR__ . '/Migrations/'
    ];

    protected array $scheduleTasks = [
        'stat-sync-users' => [
            'handler' => [StatService::class, 'syncUsers'],
            'interval' => 3600,
            'type' => TaskTypeStorage::PERIODIC,
        ]
    ];

    /**
     * @throws Exception
     */
    public function update(StatEntity $statEntity): bool|StatEntity
    {
        $stat = StatQuery::createFromEntity($statEntity)->setQueryType(StatQueryTypeStorage::UPDATE);

        return $this->service->updateStat($stat);
    }

    /**
     * @throws Exception
     */
    public function reCalcLevel(LevelCalculation $levelCalculation, string $userId, string $serverId): void
    {
        $levelCalculation = $this->levelCalculationService->levelCalculate($levelCalculation);

        $statQuery = StatQuery::create([
            'userId' => $userId,
            'serverId' => $serverId,
            'queryType' => StatQueryTypeStorage::GET,

        ]);

        $stat = $this->getService()->getStat($statQuery);

        if ($stat !== null) {
            $statQuery->setQueryType(StatQueryTypeStorage::UPDATE)->setId($stat->st_id);

            $this->getService()->updateStat($statQuery);
        }
    }

    public function levelCalculationService(): LevelCalculationService
    {
        return $this->levelCalculationService;
    }
}
