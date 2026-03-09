<?php

namespace App\Support;

final class CacheKeys
{
    public static function usersAll(): string
    {
        return 'management:users:all';
    }

    public static function adminsAll(): string
    {
        return 'management:admins:all';
    }

    public static function user(int $userId): string
    {
        return 'management:users:' . $userId;
    }

    public static function admin(int $userId): string
    {
        return 'management:admins:' . $userId;
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
