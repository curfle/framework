<?php

namespace Curfle\Support\Facades;

/**
 * @method static string method()
 * @method static string uri()
 * @method static string host()
 * @method static bool https()
 * @method static array headers()
 * @method static string ip()
 * @method static bool hasInput(string $name)
 * @method static mixed input(string $name)
 * @method static Request addInput(string $name, mixed $value)
 *
 * @see \Curfle\Http\Request
 */
class Request extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getSingletonId(): string
    {
        return "request";
    }
}