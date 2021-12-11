<?php

namespace Curfle\Support\Env;

use Curfle\Support\Str;

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
            if (str_starts_with(Str::trim($line), '#')) {
                continue;
            }

            // obtain $name and $value
            [$name, $value] = Str::split($line, '=', 2);
            $name = Str::trim($name);
            $value = Str::trim($value);

            // removes quotes like ""
            if (str_starts_with($value, '"'))
                $value = Str::substring(
                    $value,
                    1,
                    (Str::find($value, '"', 1) ?: Str::length($value) + 1) - 1
                );
            // else use until first space or #
            else
                $value = Str::split(Str::split($value, "#")[0], " ")[0];

            if (!array_key_exists($name, $result)) {
                $result[$name] = $value;
            }
        }

        return $result;
    }
}