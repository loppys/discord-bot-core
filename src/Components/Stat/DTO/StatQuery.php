<?php

namespace Discord\Bot\Components\Stat\DTO;

use Discord\Bot\Components\Stat\Entity\StatEntity;
use Discord\Bot\Components\Stat\Storages\StatQueryTypeStorage;
use Discord\Bot\System\Traits\DefaultObjectCreatorTrait;

class StatQuery
{
    use DefaultObjectCreatorTrait;

    protected int $queryType = StatQueryTypeStorage::EMPTY;

    protected ?int $type = null;

    protected string $name = '';

    protected ?int $id = null;

    protected ?string $value = null;

    protected ?string $userId = null;

    protected ?string $serverId = null;

    protected ?int $level = 1;

    protected ?float $currentExp = 0;

    protected ?float $nextExp = 100;

    protected ?float $multiplier = 1.5;

    protected ?int $msgCount = 0;

    protected ?int $badMsg = 0;

    protected array $columnMap = [
        '__stat' => [
            'st_type' => 'type',
            'st_name' => 'name',
            'st_value' => 'value',
            'st_usr_id' => 'userId',
            'st_srv_id' => 'serverId',
        ],
        '__stat_level' => [
            'stl_st_id' => '__debug__join_st_id',
            'stl_lvl' => 'level',
            'stl_current_exp' => 'currentExp',
            'stl_next_exp' => 'nextExp',
            'stl_multiplier' => 'multiplier',
        ],
        '__stat_messages' => [
            'stm_st_id' => '__debug__join_st_id',
            'stm_msg_count' => 'msgCount',
            'stm_bad_msg' => 'badMsg',
        ]
    ];

    public function __construct(string $name = '', int $queryType = StatQueryTypeStorage::EMPTY)
    {
        $this->queryType = $queryType;

        if (empty($name)) {
            $this->name = uniqid('gen__');
        } else {
            $this->name = $name;
        }
    }

    public static function createFromEntity(StatEntity $entity): static
    {
        $obj = static::create();
        $columnMap = $obj->getColumnMap();

        $data = [];
        foreach ($columnMap as $map) {
            foreach ($map as $key => $propertyName) {
                $data[$propertyName] = $entity->getDataByName($key);
            }
        }

        return $obj->dataFill($data);
    }

    public function dataFill(array $data = []): static
    {
        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            } elseif (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }

        return $this;
    }

    public function compareDataArray(): array
    {
        if ($this->queryType === StatQueryTypeStorage::EMPTY) {
            return [];
        }

        $result = [];

        foreach ($this->columnMap as $table => $columnMap) {
            foreach ($columnMap as $column => $property) {
                if ($property === '__debug__join_st_id') {
                    $propertyValue = '__debug__join_st_id';
                } else {
                    $propertyValue = $this->{$property};
                }

                if ($propertyValue === null) {
                    continue;
                }

                $result[$table][$column] = $propertyValue;
            }
        }

        return $result;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getQueryType(): int
    {
        return $this->queryType;
    }

    public function setQueryType(int $queryType): static
    {
        $this->queryType = $queryType;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isNameGenerated(): bool
    {
        [$prefix] = explode('__', $this->name);

        return $prefix === 'gen';
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getServerId(): ?string
    {
        return $this->serverId;
    }

    public function setServerId(?string $serverId): static
    {
        $this->serverId = $serverId;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getCurrentExp(): ?float
    {
        return $this->currentExp;
    }

    public function setCurrentExp(?float $currentExp): static
    {
        $this->currentExp = $currentExp;

        return $this;
    }

    public function getNextExp(): ?float
    {
        return $this->nextExp;
    }

    public function setNextExp(?float $nextExp): static
    {
        $this->nextExp = $nextExp;

        return $this;
    }

    public function getMultiplier(): ?float
    {
        return $this->multiplier;
    }

    public function setMultiplier(?float $multiplier): static
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    public function getMsgCount(): ?int
    {
        return $this->msgCount;
    }

    public function setMsgCount(?int $msgCount): static
    {
        $this->msgCount = $msgCount;

        return $this;
    }

    public function getBadMsg(): ?int
    {
        return $this->badMsg;
    }

    public function setBadMsg(?int $badMsg): static
    {
        $this->badMsg = $badMsg;

        return $this;
    }

    public function isCreate(): bool
    {
        return $this->queryType === StatQueryTypeStorage::CREATE;
    }

    public function isGet(): bool
    {
        return $this->queryType === StatQueryTypeStorage::GET;
    }

    public function isUpdate(): bool
    {
        return $this->queryType === StatQueryTypeStorage::UPDATE;
    }

    public function isDelete(): bool
    {
        return $this->queryType === StatQueryTypeStorage::DELETE;
    }

    public function getColumnMap(): array
    {
        return $this->columnMap;
    }
}
