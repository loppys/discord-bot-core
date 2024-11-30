<?php

namespace Discord\Bot\System\Repository;

use Discord\Bot\System\DBAL;
use Discord\Bot\System\Repository\DTO\Criteria;
use Discord\Bot\System\Repository\Storage\CriteriaOperatorStorage;

class CriteriaComparator
{
    protected DBAL $db;

    public function __construct(DBAL $db)
    {
        $this->db = $db;
    }

    public function compare(Criteria $criteria): string
    {
        $key = $this->db->escapeValue($criteria->getKey(), true);

        $value = $criteria->getValue();

        if (is_array($value)) {
            $criteria->setOperator(CriteriaOperatorStorage::IN);
        }

        $value = $this->db->escapeValue($value);

        if ($criteria->getOperator() === CriteriaOperatorStorage::IN) {
            $value = implode(',', $value);

            $value = "({$value})";
        }

        return "{$key} {$criteria->getOperator()} {$value}";
    }
}
