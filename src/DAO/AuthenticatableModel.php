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
    public function getIdentifier(): mixed
    {
        return $this->id;
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
        $username = $credentials[static::getUsernameColumnForAuth()] ?? null;
        $password = $credentials[static::getPasswordColumnForAuth()] ?? null;

        // obtain hash from model
        $hash = static::where(static::getUsernameColumnForAuth(), $username)
            ->valueAs(static::getPasswordColumnForAuth(), "hash")
            ->first()["hash"] ?? null;

        // return false if no matching user was found
        if($hash === null)
            return false;

        // verify hash against password
        return Hash::verify($password, $hash);
    }
}