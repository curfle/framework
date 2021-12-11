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
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function add(array $array, string $key, mixed $value): array
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param iterable $array
     * @param string $prepend
     * @return array
     */
    public static function dot(iterable $array, string $prepend = ""): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Get all the given array except for a specified array of keys.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function except(array $array, array|string $keys): array
    {
        static::forget($array, $keys);

        return $array;
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
     * Return the first element in an array passing a given truth test.
     *
     * @param iterable $array
     * @param callable|null $callback
     * @param mixed|null $default
     * @return mixed
     */
    public static function first(iterable $array, callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return (is_callable($default) ? $default() : $default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return (is_callable($default) ? $default() : $default);
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array $array
     * @param callable|null $callback
     * @param mixed|null $default
     * @return mixed
     */
    public static function last(array $array, callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? (is_callable($default) ? $default() : $default) : end($array);
        }

        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    public static function forget(array &$array, array|string $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = Str::split($key, '.');

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param ArrayAccess|array $array
     * @param int|string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(ArrayAccess|array $array, int|string|null $key, mixed $default = null): mixed
    {
        if (!static::accessible($array)) {
            return (is_callable($default) ? $default() : $default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $array[$key] ?? (is_callable($default) ? $default() : $default);
        }

        foreach (Str::split($key, '.') as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return (is_callable($default) ? $default() : $default);
            }
        }

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

        if (!$array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (Str::split($key, '.') as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed|null $key
     * @return array
     */
    public static function
    prepend(array $array, mixed $value, mixed $key = null): array
    {
        if (func_num_args() == 2) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Convert the array into a query string.
     *
     * @param array $array
     * @return string
     */
    public static function query(array $array): string
    {
        return http_build_query($array, "", '&', PHP_QUERY_RFC3986);
    }

    /**
     * Set an array item to a given value using "dot" notation.
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
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = Str::split($key, '.');

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Sort the array using the given callback or "dot" notation.
     *
     * @param array $array
     * @param callable|string|null $callback
     * @return array
     */
    public static function sort(array $array, callable|string $callback = null): array
    {
        $copy = $array;
        usort($copy, $callback ?? function($a, $b){return $b-$a;});
        return $copy;
    }

    /**
     * Filter the array using the given callback.
     *
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param mixed $value
     * @return array
     */
    public static function wrap(mixed $value): array
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }
}