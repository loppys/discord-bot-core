<?php

namespace Discord\Bot\System\License;

use Discord\Bot\Scheduler\Parts\Executor;
use Discord\Bot\Scheduler\Parts\PeriodicTask;
use Vengine\Libraries\Console\ConsoleLogger;
use Discord\Bot\System\License\DTO\Key;

trait LicenseInjection
{
    use LicenseValidator;

    /**
     * @var Key[]
     */
    private array $keys;

    /**
     * @return Key[]|Key|null
     */
    public function getKey(string $guild = 'default', ?string $component = null): array|Key|null
    {
        if ($component !== null) {
            foreach ((array)$this->keys[$guild] as $key) {
                if ($key->getComponentInfo()?->getComponentName() === $component) {
                    if ($key->isExpired()) {
                        ConsoleLogger::showMessage('Warning: key ' . $key->getValue() . ' is expired');
                    }

                    return $key;
                }
            }

            return null;
        }

        return $this->keys[$guild] ?? null;
    }

    protected function _licenseInjection(?string $guild = null): static
    {
        if ($guild === null) {
            return $this;
        }

        $this->keys[$guild] = $this->licenseManager->getAllKeys($guild);

        if (empty($this->keys[$guild])) {
            ConsoleLogger::showMessage("Warning {$this->getComponentName()}: Guild {$guild} has no keys.");
        }

        $this->_setInjectionTask($guild);

        return $this;
    }

    private function _setInjectionTask(string $guild): void
    {
        $taskName = "license_injection_{$guild}";

        if ($this->scheduleManager->hasTask($taskName) !== null) {
            return;
        }

        $executor = (new Executor())
            ->setCallable([$this, '_licenseInjection'])
            ->setArguments([$guild])
        ;

        $task = (new PeriodicTask())
            ->setPeriodicInterval(900)
            ->setName($taskName)
            ->setExecutor($executor)
        ;

        $this->scheduleManager->addTask($task);
    }
}
