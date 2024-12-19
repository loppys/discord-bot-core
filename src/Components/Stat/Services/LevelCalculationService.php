<?php

namespace Discord\Bot\Components\Stat\Services;

use Discord\Bot\Components\Stat\DTO\LevelCalculation;

class LevelCalculationService
{
    public function levelCalculate(LevelCalculation $levelCalculation): LevelCalculation
    {
        if ($levelCalculation->getAddLevels() !== null) {
            $this->toLevel($levelCalculation->getAddLevels(), $levelCalculation);
        }

        if ($levelCalculation->getAddExp() > 0) {
            $levelCalculation->setCurrentExp(
                $levelCalculation->getCurrentExp() + $levelCalculation->getAddExp()
            );

            return $this->reCalculateLevel($levelCalculation);
        }

        return $levelCalculation;
    }

    protected function calculateNextExp(LevelCalculation $levelCalculation): LevelCalculation
    {
        $levelCalculation->setNextExp(
            $levelCalculation->getNextExp() * $levelCalculation->getMultiplier()
        );

        return $levelCalculation;
    }

    protected function toLevel(int $targetLevel, LevelCalculation $levelCalculation): float
    {
        while ($levelCalculation->getLevel() !== $targetLevel) {
            $levelCalculation->setCurrentExp(
                $levelCalculation->getNextExp()
            );

            $this->reCalculateLevel($levelCalculation);
        }
    }

    protected function reCalculateLevel(LevelCalculation $levelCalculation): LevelCalculation
    {
        while ($levelCalculation->getCurrentExp() >= $levelCalculation->getNextExp()) {
            $this->calculateNextExp($levelCalculation);

            $levelCalculation->setLevel(
                $levelCalculation->getLevel() + 1
            );
        }

        return $levelCalculation;
    }
}
