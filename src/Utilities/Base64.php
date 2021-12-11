<?php

namespace Curfle\Utilities;

use Curfle\Support\Str;

class Base64
{
    /**
     * Encodes a string in base 64 format.
     *
     * @param string $string
     * @return string|null
     */
    public static function encode(string $string): string|null
    {
        return base64_encode($string);
    }

    /**
     * Decodes a string in base 64 format.
     *
     * @param string $string
     * @return string|null
     */
    public static function decode(string $string): string|null
    {
        return base64_decode($string);
    }

    /**
     * Url encodes a string in base 64 format.
     *
     * @param string $string
     * @return string|null
     */
    public static function urlEncode(string $string): string|null
    {
        return Str::replace(
            self::encode($string),
            ["+", "/", "="],
            ["-", "_", ""]
        );
    }

    /**
     * Url decodes a string in base 64 format.
     *
     * @param string $string
     * @return string|null
     */
    public static function urlDecode(string $string): string|null
    {
        return self::decode(
            Str::replace(
                $string,
                ["-", "_", ""],
                ["+", "/", "="]
            )
        );
    }
}