<?php

namespace Discord\Bot\System\Remote\Interfaces;

interface SshClientInterface extends ClientInterface
{
    public function upload(string $localPath, string $remotePath): bool;
    public function download(string $remotePath, string $localPath): bool;
}
