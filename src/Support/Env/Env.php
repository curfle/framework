<?php

namespace Curfle\Support\Env;

use Closure;

class Env
{
    /**
     * Indicates if the putenv adapter is enabled.
     *
     * @var bool
     */
    protected static bool $putenv = true;

    /**
     * The environment repository instance.
     *
     * @var Repository|null
     */
    protected static ?Repository $repository = null;

    /**
     * Get the environment repository instance.
     *
     * @return Repository
     */
    public static function getRepository(): Repository
    {
        if (static::$repository === null) {
            static::$repository = new Repository();
        }

        return static::$repository;
    }

    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::getRepository()->get($key) ?? $default;
        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => "",
            'null', '(null)' => null,
            default => $value,
        };
    }

    /**
     * Sets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set(string $key, mixed $value = null): bool
    {
        return static::getRepository()->set($key, $value);
    }
}