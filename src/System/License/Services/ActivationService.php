<?php

namespace Discord\Bot\System\License\Services;

use Discord\Bot\System\GlobalRepository\Traits\LogSourceTrait;
use Discord\Bot\System\License\DTO\ComponentInfo;
use Discord\Bot\System\License\DTO\Key;
use Discord\Bot\System\License\DTO\KeyPeriod;
use Discord\Bot\System\License\Entities\LicenseEntity;
use Discord\Bot\System\License\Helpers\KeyGen;
use Discord\Bot\System\License\Repositories\LicenseRepository;
use Discord\Bot\System\License\Storages\KeyPrefixStorage;
use Discord\Bot\System\Logger;
use Exception;

class ActivationService
{
    use LogSourceTrait;

    protected LicenseRepository $licenseRepository;

    protected KeyGen $keyGen;

    protected Logger $logger;

    public function __construct(
        LicenseRepository $licenseRepository,
        Logger $logger,
        KeyGen $keyGen
    ) {
        $this->licenseRepository = $licenseRepository;
        $this->logger = $logger;
        $this->keyGen = $keyGen;

        $this->setLogSource('ActivationService');
    }

    /**
     * @throws Exception
     */
    public function newKey(
        string $guild,
        KeyPeriod $period,
        ?ComponentInfo $componentInfo = null,
        bool $trial = false,
        bool $universe = false,
        bool $master = false
    ): Key {
        $newKey = $this->keyGen->simpleGenerate();

        $this->logger->write("{$newKey} успешно сгенерирован");

        return (new Key())
            ->setValue($newKey)
            ->setGuild($guild)
            ->setPeriod($period)
            ->setComponentInfo($componentInfo)
            ->setMaster($master)
            ->setUniverse($universe)
            ->setTrial($trial)
            ;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function trialActivate(Key $key): bool
    {
        return $this->activate(
            $key->addPrefix(KeyPrefixStorage::TRIAL)->setTrial(true)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function componentActivate(Key $key): bool
    {
        if ($key->getComponentInfo() === null) {
            $this->writeLog('Ключ не активирован, описание компонента нет');

            return false;
        }

        return $this->activate(
            $key->addPrefix(KeyPrefixStorage::COMPONENT)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function mainActivate(Key $key): bool
    {
        return $this->activate(
            $key->addPrefix(KeyPrefixStorage::MAIN)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function universeActivate(Key $key): bool
    {
        return $this->activate(
            $key->addPrefix(KeyPrefixStorage::UNIVERSE)->setUniverse(true)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function masterActivate(Key $key): bool
    {
        return $this->activate(
            $key->addPrefix(KeyPrefixStorage::MASTER)->setMaster(true)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function activate(Key $key): bool
    {
        if (empty($key->getValue())) {
            $this->writeLog('Активация не завершена, ключа нет');

            return false;
        }

        if ($this->licenseRepository->has(['lns_key' => $key->getValue()])) {
            $this->writeLog('Ключ уже существует');

            return false;
        }

        $entity = $this->licenseRepository->newEntity();

        $entity->lns_guild = $key->getGuild();
        $entity->lns_universe = $key->isUniverse();
        $entity->lns_key = $key->getValue();
        $entity->lns_infinity = $key->getPeriod()->isInfinity();
        $entity->lns_trial = $key->isTrial();
        $entity->lns_master = $key->isMaster();
        $entity->lns_time_end = $key->getPeriod()->getTo();
        $entity->lns_time_activate = $key->getPeriod()->getFrom();

        if ($key->getComponentInfo() !== null) {
            $entity->lns_component_class = $key->getComponentInfo()->getComponentClass();
            $entity->lns_use_component_class = $key->getComponentInfo()->isUseComponentClass();
            $entity->lns_component_name = $key->getComponentInfo()->getComponentName();
        }

        if ($this->licenseRepository->saveByEntity($entity)) {
            $this->writeLog("Ключ {$key->getValue()} активирован для {$key->getGuild()}");

            return true;
        }

        return false;
    }

    public function writeLog(string $value): bool
    {
        return $this->logger->write($value);
    }

    public function getLicenseRepository(): LicenseRepository
    {
        return $this->licenseRepository;
    }
}
