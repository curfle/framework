<?php

namespace Curfle\Support\Env;

use Closure;
use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Support\Str;

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
     * Holds all seen variables during resolving to prevent a circular
     * dependency recursion problem while execution.
     *
     * @var array
     */
    protected static array $seenVariables = [];

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
     * @throws CircularDependencyException
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (in_array($key, static::$seenVariables))
            throw new CircularDependencyException("The variable [$key] cannot be resolved as its' resolution results in a circular dependency of [" . implode("→", self::$seenVariables) . "→...].");

        $mayCleanSeenVariables = empty(static::$seenVariables);
        static::$seenVariables[] = $key;

        $value = static::getRepository()->get($key) ?? $default;

        // check for variables and resolve them
        preg_match_all('/\$(_|[A-Z])+/m', $value, $matches, PREG_OFFSET_CAPTURE, 0);
        for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
            // get variable name and index
            $match = $matches[0][$i];
            $var = Str::substring($match[0], 1);
            $index = $match[1];
            // check if vraible is not an escaped dollar sign
            if ($index == 0 || ($index > 0 && $value[$index - 1] !== "\\")) {
                // get variable value
                $varValue = static::get($var, "");
                // replace in string
                $value = Str::substring($value, 0, $index)
                    . $varValue
                    . Str::substring($value, $index + Str::length($var) + 1);
            }
        }

        // clean seen vraibles in end of recursion
        if($mayCleanSeenVariables)
            static::$seenVariables = [];

        return match (Str::lower($value)) {
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