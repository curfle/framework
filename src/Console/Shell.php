<?php

namespace Curfle\Console;

use Curfle\Support\Str;

class Shell
{
    /**
     * Executes a shell command.
     *
     * @param string $command
     * @return string|false|null
     */
    public static function run(string $command): string|false|null
    {
        return Str::trim(shell_exec($command));
    }

    /**
     * Executes a buddy command.
     *
     * @param string $command
     * @return string|false|null
     */
    public static function runCommand(string $command): string|false|null
    {
        $binary = PHP_BINARY;
        $file = base_path("buddy");
        return static::run("$binary $file $command");
    }
}