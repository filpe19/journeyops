<?php

namespace App\Enums;

enum UserRole: string
{
    case Buyer = 'buyer';
    case Producer = 'producer';
    case Admin = 'admin';

    /**
     * Roles that can be chosen from the public signup form.
     *
     * @return list<string>
     */
    public static function selfService(): array
    {
        return [self::Buyer->value, self::Producer->value];
    }
}
