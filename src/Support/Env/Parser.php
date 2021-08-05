<?php

namespace Curfle\Support\Env;

class Parser{

    /**
     * Parses a .env formatted string[] and returns the according config array.
     *
     * @param array $lines
     * @return array
     */
    public static function parse(array $lines): array
    {
        $result = [];

        // iterate through the lines of the file
        foreach ($lines as $line) {

            // skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // obtain $name and $value
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if(!array_key_exists($name, $result)){
                $result[$name] = $value;
            }
        }

        return $result;
    }
}