<?php

namespace Curfle\Auth;

interface Authenticatable
{
    /**
     * Returns an instance by an identifier.
     *
     * @param mixed $identifier
     * @return ?$this
     */
    public static function fromIdentifier(mixed $identifier): ?static;

    /**
     * Attempts to log in a user with given credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public static function attempt(array $credentials) : bool;
}