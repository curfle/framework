<?php

namespace Curfle\Utilities;

class JSON
{
    /**
     * Parses a JSON string into an array or object.
     *
     * @param string $json
     * @param bool $toArray
     * @return array|object|null
     */
    public static function parse(string $json, bool $toArray = true): array|object|null
    {
        return json_decode($json, $toArray);
    }

    /**
     * Generates a JSON string from an array or object.
     *
     * @param array|object $data
     * @return string
     */
    public static function from(array|object $data): string
    {
        return json_encode($data);
    }
}