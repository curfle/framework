<?php

namespace Curfle\Support\Env;

class Parser
{

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

            // removes quotes like ""
            if (str_starts_with($value, '"'))
                $value = substr($value, 1, (strpos($value, '"', 1) ?: strlen($value) + 1) - 1);
            // else use until first space or #
            else
                $value = explode(" ", explode("#", $value)[0])[0];

            if (!array_key_exists($name, $result)) {
                $result[$name] = $value;
            }
        }

        return $result;
    }
}