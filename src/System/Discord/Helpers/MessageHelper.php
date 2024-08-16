<?php

namespace Discord\Bot\System\Discord\Helpers;

class MessageHelper
{
    public static function compareDiscordMention(string $text = '', bool $channel = false): string
    {
        $char = $channel ? '#' : '@';

        return "<{$char}{$text}>";
    }
}