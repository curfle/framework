<?php

namespace Curfle\Support\Facades;

use Curfle\Essence\Application;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Support\Exceptions\Misc\RuntimeException;
use ReflectionException;

abstract class Facade
{
    /**
     * Instance of the application
     * @var Application
     */
    protected static Application $app;

    /**
     * Instance of the facade's root instance
     * @var array
     */
    protected static array $instance = [];

    /**
     * Returns the id of the singleton that is being returned
     * @return mixed
     */
    protected abstract static function getFacadeAccessor(): mixed;

    /**
     * Sets the facade's application reference
     * @param Application $app
     */
    public static function setFacadeApplication(Application $app)
    {
        static::$app = $app;
    }

    /**
     * Returns the aliased singleton instance of the facade
     * @return object
     */
    public static function getFacadeInstance(): object
    {
        $name = static::getFacadeAccessor();

        if(is_object($name))
            return $name;

        if (isset(static::$instance[$name]))
            return static::$instance[$name];

        return static::$instance[$name] = static::$app->make($name);
    }

    /**
     * Handle dynamic, static calls to the facade's singleton instance
     * @param string $method
     * @param array $args
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = static::getFacadeInstance();

        if (!$instance) {
            throw new RuntimeException("A facade root has not been set.");
        }

        return $instance->$method(...$args);
    }
}