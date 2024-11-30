<?php

namespace Discord\Bot\Components\Stat\Services;

use Discord\Bot\Components\Stat\Entity\StatEntity;
use Discord\Bot\Components\Stat\DTO\StatQuery;
use Discord\Bot\Components\Stat\Repositories\LevelsRepository;
use Discord\Bot\Components\Stat\Repositories\MessagesRepository;
use Discord\Bot\Components\Stat\Repositories\StatRepository;
use Discord\Bot\Components\Stat\Storages\StatQueryTypeStorage;
use Discord\Bot\Components\Stat\Storages\StatTypeStorage;
use Discord\Bot\Components\Access\Storage\BaseAccessStorage;
use Discord\Bot\Core;
use Discord\Bot\System\Repository\AbstractRepository;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use Doctrine\DBAL\Exception;

class StatService
{
    protected StatRepository $statRepository;

    protected LevelsRepository $levelsRepository;

    protected MessagesRepository $messagesRepository;

    /**
     * @var AbstractRepository[]
     */
    private array $repositoryTable = [
        '__stat' => null,
        '__stat_level' => null,
        '__stat_messages' => null,
    ];

    private array $primaryColumns = [
        '__stat' => 'st_id',
        '__stat_level' => 'stl_st_id',
        '__stat_messages' => 'stm_st_id',
    ];

    public function __construct(
        StatRepository $statRepository,
        LevelsRepository $levelsRepository,
        MessagesRepository $messagesRepository
    ) {
        $this->statRepository = $statRepository;
        $this->levelsRepository = $levelsRepository;
        $this->messagesRepository = $messagesRepository;

        $this->repositoryTable['__stat'] = $statRepository;
        $this->repositoryTable['__stat_level'] = $levelsRepository;
        $this->repositoryTable['__stat_messages'] = $messagesRepository;
    }

    /**
     * @throws Exception
     */
    public function getIdByName(string $name): ?int
    {
        $data = $this->statRepository->createEntity([
            'st_name' => $name
        ]);

        if ($data === null || empty($data->st_id)) {
            return null;
        }

        return $data->st_id;
    }

    /**
     * @throws Exception
     */
    public function createNewStat(StatQuery $query): bool
    {
        if (!$query->isCreate()) {
            return false;
        }

        if (!$this->action($query)) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function getStatByCriteria(array $criteria = []): ?StatEntity
    {
        return $this->statRepository->createEntity($criteria);
    }

    /**
     * @throws Exception
     */
    public function getStat(StatQuery $query): ?StatEntity
    {
        if (!$query->isGet()) {
            return null;
        }

        if ($query->getId() === null && $query->isNameGenerated()) {
            return null;
        }

        $id = $query->getId();
        if ($id === null && !$query->isNameGenerated()) {
            $id = $this->getIdByName($query->getName());
        }

        if ($id === null) {
            return null;
        }

        $entity = $this->statRepository->createEntity([
            'st_id' => $id
        ]);

        return $entity ?? null;
    }

    /**
     * @throws Exception
     */
    public function updateStat(StatQuery $query): bool|StatEntity
    {
        if (!$query->isUpdate()) {
            return false;
        }

        if (!$this->action($query)) {
            return false;
        }

        $newEntity = $this->statRepository->createEntity([
            'st_id' => $query->getId()
        ]);

        if ($newEntity === null) {
            return false;
        }

        return $newEntity;
    }

    /**
     * @throws Exception
     */
    public function deleteStat(StatQuery $query): bool
    {
        if (!$query->isDelete()) {
            return false;
        }

        if ($query->getId() === null) {
            return false;
        }

        return $this->statRepository->delete(['st_id' => $query->getId()]);
    }

    /**
     * @throws Exception
     */
    protected function action(StatQuery $query): bool
    {
        $actionMethodList = [
            StatQueryTypeStorage::CREATE => 'create',
            StatQueryTypeStorage::UPDATE => 'update',
        ];

        if (empty($actionMethodList[$query->getQueryType()])) {
            return false;
        }

        $actionMethod = $actionMethodList[$query->getQueryType()];

        $countFail = 0;
        $mainId = 0;
        foreach ($query->compareDataArray() as $table => $data) {
            $res = true;

            if ($actionMethod === 'create') {
                if (!empty($data['stm_st_id']) && !is_int($data['stm_st_id'])) {
                    $data['stm_st_id'] = $mainId;
                }

                if (!empty($data['stl_st_id']) && !is_int($data['stl_st_id'])) {
                    $data['stl_st_id'] = $mainId;
                }

                $res = $this->repositoryTable[$table]->save(
                    $data
                );

                if (empty($mainId)) {
                    $mainId = $this->statRepository->getLastInsertId();
                }
            }

            if ($actionMethod === 'update') {
                if ($query->getId() === null) {
                    $countFail++;

                    continue;
                }

                $res = $this->repositoryTable[$table]->update(
                    $data,
                    [
                        $this->primaryColumns[$table] => $query->getId()
                    ]
                );
            }

            if ($res === false) {
                $countFail++;
            }
        }

        return $countFail === 0;
    }

    /**
     * @throws Exception
     */
    public function syncUsers(): bool
    {
        /** @var Guild $guild */
        foreach (Core::getInstance()->getDiscord()->guilds->toArray() as $guild) {
            /** @var Member $member */
            foreach ($guild->members->toArray() as $member) {
                $userId = $member->id;
                if (!Core::getInstance()->components->user->hasUser($userId)) {
                    $group = BaseAccessStorage::USER;

                    if ($member->guild->owner_id === $userId) {
                        $group = BaseAccessStorage::OWNER;
                    }

                    if (empty($userId)) {
                        continue;
                    }

                    $user = Core::getInstance()->components->user->register(
                        $userId,
                        $member->guild_id,
                        $group
                    );

                    if ($user === null) {
                        continue;
                    }

                    $query = new StatQuery();

                    $query
                        ->setUserId($user->usr_id)
                        ->setServerId($member->guild_id)
                        ->setType(StatTypeStorage::USER)
                        ->setQueryType(StatQueryTypeStorage::CREATE)
                    ;

                    $this->createNewStat($query);
                }
            }
        }

        return true;
    }
}
