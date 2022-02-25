<?php

namespace Curfle\Support;

class Str
{

    /**
     * Returns whether the argument is a string.
     *
     * @param string $string
     * @return bool
     */
    public static function is(string $string): bool
    {
        return is_string($string);
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param string $haystack
     * @param string|string[] $needles
     * @return bool
     */
    public static function contains(string $haystack, array|string $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== "" && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param string|string[] $needles
     * @return bool
     */
    public static function startsWith(string $haystack, array|string $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle !== "" && strncmp($haystack, $needle, static::length($needle)) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the part of the string before the first occurence of the needle.
     *
     * @param string $haystack
     * @param string|int $needle
     * @return string
     */
    public static function until(string $haystack, string|int $needle): string
    {
        if (!static::contains($haystack, $needle))
            return $haystack;

        if (is_string($needle))
            $needle = static::find($haystack, $needle);

        return static::substring($haystack, 0, $needle);
    }

    /**
     * Returns the part of the string after the first occurence of the needle.
     *
     * @param string $haystack
     * @param string|int $needle
     * @return string
     */
    public static function from(string $haystack, string|int $needle): string
    {
        if (!static::contains($haystack, $needle))
            return $haystack;

        if (is_string($needle))
            $needle = static::find($haystack, $needle) + 1;

        return static::substring($haystack, $needle);
    }

    /**
     * Returns a substring of a string based on indices.
     *
     * @param string $string
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function substring(string $string, int $start, int $length = null): string
    {
        return substr($string, $start, $length);
    }

    /**
     * Splits the string by a given seperator.
     *
     * @param string $string
     * @param string $seperator
     * @param int $limit
     * @return string[]
     */
    public static function split(string $string, string $seperator = " ", int $limit = PHP_INT_MAX): array
    {
        return explode($seperator, $string, $limit);
    }

    /**
     * Concatenates an array into a string by a given seperator.
     *
     * @param array $array
     * @param string $seperator
     * @return string
     */
    public static function concat(array $array, string $seperator = " "): string
    {
        return implode($seperator, $array);
    }

    /**
     * Returns the length of a string.
     *
     * @param string $string
     * @return int
     */
    public static function length(string $string): int
    {
        return strlen($string);
    }

    /**
     * Returns wether the string is empty or not.
     *
     * @param string $string
     * @return bool
     */
    public static function empty(string $string): bool
    {
        return $string === "";
    }

    /**
     * Replaces all occurences of a string.
     *
     * @param string|array $string
     * @param array|string $search
     * @param array|string $replace
     * @param int|null $count
     * @return string
     */
    public static function replace(string|array $string, array|string $search, array|string $replace, int &$count = null): string
    {
        return str_replace($search, $replace, $string, $count);
    }


    /**
     * Strips whitespaces or specified characters from the beginning and the end of a string.
     *
     * @param string $string |array $string
     * @param string $characters
     * @return string
     */
    public static function trim(string $string, string $characters = " \n\r\t\v\0"): string
    {
        return trim($string, $characters);
    }

    /**
     * Returns the position of a substring in a string.
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @return int
     */
    public static function find(string $haystack, string $needle, int $offset = 0): int
    {
        return strpos($haystack, $needle, $offset);
    }

    /**
     * Makes a string lowercase.
     *
     * @param string $string
     * @return string
     */
    public static function lower(string $string): string
    {
        return strtolower($string);
    }

    /**
     * Makes a string uppercase.
     *
     * @param string|null $string
     * @return string
     */
    public static function upper(?string $string): string
    {
        return strtoupper($string);
    }
}