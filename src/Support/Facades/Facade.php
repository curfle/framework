<?php

namespace Curfle\Support\Facades;

use Curfle\Essence\Application;
use Curfle\Support\Exceptions\BindingResolutionException;
use Curfle\Support\Exceptions\RuntimeException;

abstract class Facade
{
    /**
     * Instance of the application
     * @var Application
     */
    protected static Application $app;

    /**
     * Instance of the facade's root instance
     * @var object
     */
    protected static object $instance;

    /**
     * Returns the id of the singleton that is being returned
     * @return string
     */
    protected abstract static function getSingletonId(): string;

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
     * @throws BindingResolutionException
     */
    public function getFacadeInstance(): object
    {
        if (isset(static::$instance))
            return static::$instance;

        return static::$instance = static::$app->resolve(static::getSingletonId());
    }

    /**
     * Handle dynamic, static calls to the facade's singleton instance
     * @param string $method
     * @param array $args
     * @return mixed
     *
     * @throws RuntimeException
     * @throws BindingResolutionException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeInstance();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}