<?php

namespace App\Support;

final class CacheKeys
{
    public static function usersAll(): string
    {
        return 'management:users:all';
    }

    public static function user(int $userId): string
    {
        return 'management:users:' . $userId;
    }

    public static function locationsAll(): string
    {
        return 'management:locations:all';
    }

    public static function location(int $locationId): string
    {
        return 'management:locations:' . $locationId;
    }
}
