<?php

namespace Curfle\Support\Facades;

/**
 * @method static \Curfle\Routing\Route any(string $uri, array|string|callable|null $action = null)
 * @method static \Curfle\Routing\Route fallback(array|string|callable|null $action = null)
 * @method static \Curfle\Routing\Route get(string $uri, array|string|callable|null $action = null)
 * @method static \Curfle\Routing\Route options(string $uri, array|string|callable|null $action = null)
 * @method static \Curfle\Routing\Route patch(string $uri, array|string|callable|null $action = null)
 * @method static \Curfle\Routing\Route post(string $uri, array|string|callable|null $action = null)
 * @method static \Curfle\Routing\Route put(string $uri, array|string|callable|null $action = null)
 * @method static \Curfle\Routing\Route redirect(string $uri, string $target, int $code = 302)
 * @method static \Curfle\Routing\Router registerRouteFile(string $filename)
 *
 * @see \Curfle\Routing\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() : string
    {
        return 'router';
    }
}