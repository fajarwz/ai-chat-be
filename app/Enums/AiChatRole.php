<?php

namespace App\Enums;

enum AiChatRole: string
{
    case User = 'user';
    case Assistant = 'assistant';

    public static function values(): array
    {
        return [
            self::User->value,
            self::Assistant->value,
        ];
    }
}
