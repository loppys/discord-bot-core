<?php

namespace Discord\Bot\System\Remote\Clients;

use Discord\Bot\Core;
use Discord\Bot\System\Remote\AbstractClient;
use Discord\Bot\System\Remote\Interfaces\SshClientInterface;
use phpseclib3\Net\SFTP;
use React\EventLoop\TimerInterface;
use Vengine\Libraries\Console\ConsoleLogger;

class SshClient extends AbstractClient implements SshClientInterface
{
    protected int $timeout = 3600;

    protected TimerInterface $timer;

    private SFTP $sftp;
    private string $username;
    private string $password;

    public function __construct(string $host, string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->sftp = new SFTP($host);
    }

    public function connect(): bool
    {
        if (!$this->sftp->login($this->username, $this->password)) {
            ConsoleLogger::showMessage('SSH login failed.');

            return false;
        }

        $this->isConnected = true;

        $this->timer = Core::getInstance()->getLoop()->addTimer($this->timeout, [$this, 'disconnect']);

        return $this->isConnected;
    }

    public function disconnect(): void
    {
        $this->sftp->disconnect();

        Core::getInstance()->getLoop()->cancelTimer($this->timer);

        $this->isConnected = false;
    }

    public function execute(string $command): string|bool
    {
        if (!$this->isConnected) {
            ConsoleLogger::showMessage('Not connected to SSH server.');

            return false;
        }

        return $this->sftp->exec($command);
    }

    public function upload(string $localPath, string $remotePath): bool
    {
        if (!$this->isConnected) {
            ConsoleLogger::showMessage('Not connected to SSH server.');

            return false;
        }

        return $this->sftp->put($remotePath, $localPath, SFTP::SOURCE_LOCAL_FILE);
    }

    public function download(string $remotePath, string $localPath): bool
    {
        if (!$this->isConnected) {
            ConsoleLogger::showMessage('Not connected to SSH server.');

            return false;
        }

        return $this->sftp->get($remotePath, $localPath);
    }
}
