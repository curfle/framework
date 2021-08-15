<?php

namespace Curfle\Utilities;

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
        return str_replace(
            ["+", "/", "="],
            ["-", "_", ""],
            self::encode($string)
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
            str_replace(
                ["+", "/", "="],
                ["-", "_", ""],
                $string
            )
        );
    }
}