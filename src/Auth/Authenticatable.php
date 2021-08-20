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
}