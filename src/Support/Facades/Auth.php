<?php

namespace Curfle\Support\Facades;

use Curfle\Auth\Authenticatable;
use Curfle\Auth\AuthenticationManager;
use Curfle\Auth\Guardians\Guardian;

/**
 * @method static Guardian guardian(string $name)
 * @method static bool validate(\Curfle\Http\Request $request)
 * @method static bool attempt(array $credentials)
 * @method static Authenticatable user(string $name = "default")
 * @method static AuthenticationManager login(Authenticatable $user, string $name = "default")
 *
 * @see \Curfle\Auth\AuthenticationManager
 */
class Auth extends Facade
{

    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor(): string
    {
        return "auth";
    }
}