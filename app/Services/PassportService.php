<?php

namespace App\Services;

use App\User;


/**
 * Class PassportService
 * @package App\Services
 */
class PassportService
{
    /**
     * @param User $user
     * @param string $grant
     * @return string
     */
    public static function token(User $user, string $grant = 'password')
    {
        return $user->createToken(ucfirst($grant) . ' Grant Client')->accessToken;
    }
}
