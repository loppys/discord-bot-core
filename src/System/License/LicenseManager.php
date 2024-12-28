<?php

namespace Discord\Bot\System\License;

use Discord\Bot\System\GlobalRepository\Traits\LogSourceTrait;
use Discord\Bot\System\License\DTO\ComponentInfo;
use Discord\Bot\System\License\DTO\Key;
use Discord\Bot\System\License\DTO\KeyPeriod;
use Discord\Bot\System\License\Repositories\LicenseRepository;
use Discord\Bot\System\License\Services\ActivationService;
use Vengine\Libraries\Repository\Server\Source;
use Discord\Bot\System\License\Storages\ActivateMethodStorage;
use Discord\Bot\System\License\Storages\KeyPrefixStorage;
use Discord\Bot\System\Logger;
use Vengine\Libraries\Repository\DTO\LikeCriteria;
use Doctrine\DBAL\Exception;

class LicenseManager
{
    use LogSourceTrait;

    protected ActivationService $activationService;

    protected LicenseRepository $licenseRepository;

    protected Logger $logger;

    public function __construct(
        ActivationService $activationService,
        LicenseRepository $licenseRepository,
        Logger $logger,
        Source $source
    ) {
        $this->activationService = $activationService;
        $this->licenseRepository = $licenseRepository;
        $this->logger = $logger;

        $this->setLicenseSource($source);
        $this->setLogSource('LicenseManager');
    }

    /**
     * @throws Exception
     */
    public function activate(Key $key, string $method = ActivateMethodStorage::MAIN): bool
    {
        $activateMethod = "{$method}Activate";

        if (method_exists($this->activationService, $activateMethod)) {
            return call_user_func([$this->getActivationService(), $activateMethod], $key);
        }

        $this->logger->write("Ключ {$key->getValue()} не активирован, метод активации указан неверно");

        return false;
    }

    /**
     * @return Key[]
     */
    public function getAllKeys(string $guild, ?string $component = null): array
    {
        $criteria = [
            'lns_guild' => $guild
        ];

        if ($component !== null) {
            $criteria['lns_component_name'] = $component;
        }

        $arr = [];
        foreach ($this->licenseRepository->getAll($criteria) as $entity) {
            $componentInfo = (new ComponentInfo())
                ->setUseComponentClass((bool)$entity->lns_use_component_class)
                ->setComponentName((string)$entity->lns_component_name)
                ->setComponentClass((string)$entity->lns_component_class)
            ;

            if ($entity->lns_infinity) {
                $period = KeyPeriod::createInfinity();
            } else {
                $period = KeyPeriod::createDefault(
                    $entity->lns_time_activate ?? time(), $entity->lns_time_end ?? time()
                );
            }

            $arr[] = (new Key())
                ->setPeriod($period)
                ->setComponentInfo($componentInfo)
                ->setTrial((bool)$entity->lns_trial)
                ->setValue($entity->lns_key)
                ->setUniverse((bool)$entity->lns_universe)
                ->setGuild($entity->lns_guild)
                ->setMaster((bool)$entity->lns_master)
            ;
        }

        return $arr;
    }

    /**
     * @throws Exception
     */
    public function getKey(
        string $guild,
        string $prefix = KeyPrefixStorage::MAIN,
        ?string $component = null
    ): ?Key {
        $criteria = [
            'lns_guild' => $guild,
            new LikeCriteria('lns_key', "{$prefix}%")
        ];

        if ($component !== null) {
            $criteria['lns_component_name'] = $component;
        }

        $entity = $this->licenseRepository->createEntity($criteria);

        if ($entity === null) {
            return null;
        }

        $componentInfo = (new ComponentInfo())
            ->setUseComponentClass((bool)$entity->lns_use_component_class)
            ->setComponentName((string)$entity->lns_component_name)
            ->setComponentClass((string)$entity->lns_component_class)
        ;

        if ($entity->lns_infinity) {
            $period = KeyPeriod::createInfinity();
        } else {
            $period = KeyPeriod::createDefault($entity->lns_time_activate ?? time(), $entity->lns_time_end ?? time());
        }

        return (new Key())
            ->setPeriod($period)
            ->setComponentInfo($componentInfo)
            ->setTrial((bool)$entity->lns_trial)
            ->setValue($entity->lns_key)
            ->setUniverse((bool)$entity->lns_universe)
            ->setGuild($entity->lns_guild)
            ->setMaster((bool)$entity->lns_master)
            ;
    }

    public function getActivationService(): ActivationService
    {
        return $this->activationService;
    }

    public function setLicenseSource(Source $source): static
    {
        $this->activationService->getLicenseRepository()->setServerSource($source);

        return $this;
    }
}
