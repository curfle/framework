<?php

namespace Curfle\DAO;

use Curfle\Auth\Authenticatable;
use Curfle\Support\Facades\Hash;

abstract class AuthenticatableModel extends Model implements Authenticatable
{
    /**
     * Returns the name of the username column for authentication.
     *
     * @return string
     */
    protected static function getUsernameColumnForAuth(): string
    {
        return "email";
    }

    /**
     * Returns the name of the password column for authentication.
     *
     * @return string
     */
    protected static function getPasswordColumnForAuth(): string
    {
        return "password";
    }

    /**
     * @inheritDoc
     */
    public static function fromIdentifier(mixed $identifier): ?static
    {
        return self::get($identifier);
    }

    /**
     * @inheritDoc
     */
    public static function attempt(mixed $credentials): bool
    {
        // get username
        $username = $credentials[self::getUsernameColumnForAuth()] ?? null;
        $password = $credentials[self::getPasswordColumnForAuth()] ?? null;

        // obtain hash from model
        $hash = self::where(self::getUsernameColumnForAuth(), $username)
            ->valueAs(self::getPasswordColumnForAuth(), "hash")
            ->first()["hash"] ?? null;

        // exit if no matching user was found
        if($hash === null)
            return false;

        // verify hash against password
        return Hash::verify($password, $hash);
    }
}