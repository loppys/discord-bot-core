<?php

namespace Discord\Bot\System\License\Helpers;

use Vengine\Libraries\Console\ConsoleLogger;
use Exception;

class KeyGen
{
    private ?string $prefix = null;

    private string $template = 'X9XX99-XX99X-9X9XX-99XX9X';

    private string $case = 'upper';

    private int $keyCount = 1;

    protected array $keys = [];

    public function __construct(
        string $prefix = null,
        string $template = null,
        string $case = 'upper',
        int $count = null
    ) {
        if (!empty($prefix)) {
            $this->prefix = $prefix;
        }
        if (!empty($template)) {
            $this->template = $template;
        }
        if (!empty($case)) {
            $this->case = $case;
        }
        if (!empty($count)) {
            $this->keyCount = $count;
        }
    }

    private function license(): string
    {
        $key = null;
        if (!empty($this->prefix)) {
            $key .= $this->prefix . '-';
        }

        for ($i = 0; $i < strlen($this->template); $i++) {
            if (preg_match('/[a-zA-Z]/', $this->template[$i])) {
                if ($this->case == 'lower') {
                    $key .= chr(rand(97, 122));
                } else {
                    $key .= chr(rand(65, 90));
                }
            } else if (preg_match('/\d/', $this->template[$i])) {
                $key .= rand(0, 9);
            } else {
                $key .= '-';
            }
        }
        return $key;
    }

    private function check(string $key = null): bool
    {
        if (in_array($key, $this->keys)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws Exception
     */
    public function multiGenerate(): array
    {
        $key = null;
        while (!$this->check($key) && count($this->keys) < $this->keyCount) {
            $this->keys[] = $this->license();
        }

        if (count($this->keys) == 0) {
            ConsoleLogger::showMessage('Не удалось сгенерировать ключи');
        }

        $result = $this->keys;

        $this->keys = [];

        return $result;
    }

    /**
     * @throws Exception
     */
    public function simpleGenerate(): string
    {
        return $this->license();
    }
}
