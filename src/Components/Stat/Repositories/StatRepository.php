<?php

namespace Discord\Bot\Components\Stat\Repositories;

use Discord\Bot\System\DBAL;
use Discord\Bot\Components\Stat\Entity\StatEntity;
use Discord\Bot\System\Repository\AbstractRepository;
use Discord\Bot\System\Repository\CriteriaComparator;
use Discord\Bot\System\Repository\DTO\DependencyTable;
use Discord\Bot\System\Repository\Schema\Table;
use Discord\Bot\System\Repository\Schema\TableField;
use Discord\Bot\System\Repository\Storage\JoinTypeStorage;

class StatRepository extends AbstractRepository
{
    protected string $table = '__stat';

    protected string $primaryKey = 'st_id';

    protected array $columnMap = [
        'st_type',
        'st_name',
        'st_value',
        'st_usr_id',
        'st_srv_id'
    ];

    protected string $entityClass = StatEntity::class;

    public function createEntity(array $criteria = []): ?StatEntity
    {
        return parent::createEntity($criteria);
    }

    public function __construct(DBAL $db, CriteriaComparator $criteriaComparator)
    {
        $this->dependencyTableList[] = (new DependencyTable())
            ->setFromTable('__stat_level')
            ->setAliasTable('stl')
            ->setFromAlias('t1')
            ->setJoinType(JoinTypeStorage::LEFT)
            ->setConditionFromColumns(['stl_st_id' => 'st_id'])
        ;

        $this->dependencyTableList[] = (new DependencyTable())
            ->setFromTable('__stat_messages')
            ->setAliasTable('stm')
            ->setFromAlias('t1')
            ->setJoinType(JoinTypeStorage::LEFT)
            ->setConditionFromColumns(['stm_st_id' => 'st_id'])
        ;

        parent::__construct($db, $criteriaComparator);

        $statLevelTable = new Table(
            '__stat_level',
            [new TableField('stlStId', 'stl_st_id')]
        );
        $statMessagesTable = new Table(
            '__stat_messages',
            [new TableField('stmStId', 'stm_st_id')]
        );

        $this->_table
            ->addTableDependecy(
                $statLevelTable->setAliasTable('stl')->setJoinMethod(JoinTypeStorage::LEFT)
            )
            ->addTableDependecy(
                $statMessagesTable->setAliasTable('stm')->setJoinMethod(JoinTypeStorage::LEFT)
            )
        ;
    }
}
