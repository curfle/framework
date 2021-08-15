<?php

namespace Curfle\Support\Facades;


use Curfle\Essence\Application;

/**
 * @method static object|string resolve(string $id, array $parameters = [])
 *
 * @see Application
 */
class App extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return object
     */
    protected static function getFacadeAccessor() : object
    {
        return app();
    }
}