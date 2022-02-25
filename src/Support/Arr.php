<?php

namespace Curfle\Support;

use ArrayAccess;

class Arr
{
    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     * @return bool
     */
    public static function is(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine whether a given value is contained in the array.
     *
     * @param array $array
     * @param mixed $value
     * @param bool $strict
     * @return bool
     */
    public static function in(array $array, mixed $value, bool $strict = false): bool
    {
        return in_array($value, $array, $strict);
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param ArrayAccess|array $array
     * @param int|string $key
     * @return bool
     */
    public static function exists(ArrayAccess|array $array, int|string $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Return whether the array is empty.
     *
     * @param array $array
     * @return bool
     */
    public static function empty(array $array): bool
    {
        return static::length($array) === 0;
    }


    /**
     * Returns the length of the array.
     *
     * @param array $array
     * @return int
     */
    public static function length(array $array): int
    {
        return count($array);
    }

    /**
     * Returns the array keys.
     *
     * @param array $array
     * @return array
     */
    public static function keys(array $array): array
    {
        return array_keys($array);
    }

    /**
     * Filters the array using the given callback.
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public static function filter(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Maps a given callback to the array.
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public static function map(array $array, callable $callback): array
    {
        return array_map($callback, $array);
    }

    /**
     * Applies a user supplied function to every member of an array.
     *
     * @param array $array
     * @param callable $callback
     * @return bool
     */
    public static function walk(array &$array, callable $callback): bool
    {
        return array_walk($array, $callback);
    }

    /**
     * Sort an array in ascending order if no function is passed. If a function is passed, it will be used for sorting
     * the array in a custom order.
     *
     * @param array $array
     * @param callable|null $fn
     * @return bool
     */
    public static function sort(array &$array, callable $fn = null): bool
    {
        return $fn === null ? sort($array) : usort($array, $fn);
    }

    /**
     * Iteratively reduces the array to a single value using a callback function.
     *
     * @param array $array
     * @param callable $callback
     * @param mixed $initial
     * @return array
     */
    public static function reduce(array $array, callable $callback, mixed $initial): array
    {
        return array_reduce($array, $callback, $initial);
    }

    /**
     * Gets an item from an array using "dot" notation.
     *
     * @param ArrayAccess|array $array
     * @param int|string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(ArrayAccess|array $array, int|string|null $key, mixed $default = null): mixed
    {
        if (!static::is($array))
            return (is_callable($default) ? $default() : $default);

        if ($key === null)
            return $array;

        if (static::exists($array, $key))
            return $array[$key];

        if (!str_contains($key, '.')) {
            return $array[$key] ?? (is_callable($default) ? $default() : $default);
        }

        foreach (Str::split($key, '.') as $segment) {
            if (static::is($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return (is_callable($default) ? $default() : $default);
            }
        }

        return $array;
    }

    /**
     * Sets an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param string|null $key
     * @param mixed $value
     * @return array
     */
    public static function set(array &$array, ?string $key, mixed $value): array
    {
        if (is_null($key))
            return $array = $value;

        $keys = Str::split($key, '.');

        foreach ($keys as $i => $key) {
            if (count($keys) === 1)
                break;

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !Arr::is($array[$key]))
                $array[$key] = [];

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param ArrayAccess|array $array
     * @param array|string $keys
     * @return bool
     */
    public static function has(ArrayAccess|array $array, array|string $keys): bool
    {
        $keys = (array)$keys;

        if (!$array || $keys === [])
            return false;

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key))
                continue;

            foreach (Str::split($key, '.') as $segment) {
                if (static::is($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}