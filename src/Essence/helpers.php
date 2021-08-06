<?php

use Curfle\Config\Repository;
use Curfle\Container\Container;
use Curfle\Support\Env\Env;

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null $id
     * @param array $parameters
     * @return string|object
     */
    function app(string $id = null, array $parameters = []): string|object
    {
        if (is_null($id)) {
            return Container::getInstance();
        }

        return Container::getInstance()->resolve($id, $parameters);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param string $path
     * @return string
     */
    function app_path(string $path = ""): string
    {
        return app()->path($path);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the path to the base of the installation.
     *
     * @param string $path
     * @return string
     */
    function base_path(string $path = ""): string
    {
        return app()->basePath($path);
    }
}

if (!function_exists('config')) {
    /**
     * Gets the specified configuration value.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    function config(string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return app('config');
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('database_path')) {
    /**
     * Get the database path.
     *
     * @param string $path
     * @return string
     */
    function database_path(string $path = ""): string
    {
        return app()->databasePath($path);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('resource_path')) {
    /**
     * Get the path to the resources' folder.
     *
     * @param string $path
     * @return string
     */
    function resource_path(string $path = ""): string
    {
        return app()->resourcePath($path);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param string $path
     * @return string
     */
    function storage_path(string $path = ""): string
    {
        return app('path.storage').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}