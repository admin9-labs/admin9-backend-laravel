<?php

namespace App\Enums;

enum OAuthProvider: string
{
    case GOOGLE = 'google';

    public static function values(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
