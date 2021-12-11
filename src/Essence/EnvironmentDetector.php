<?php

namespace Curfle\Essence;

use Closure;
use Curfle\Support\Str;

class EnvironmentDetector
{
    /**
     * Detect the application's current environment.
     *
     * @param Closure $callback
     * @param array|null $consoleArgs
     * @return string|null
     */
    public function detect(Closure $callback, array $consoleArgs = null): ?string
    {
        if ($consoleArgs) {
            return $this->detectConsoleEnvironment($callback, $consoleArgs);
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Set the application environment for a web request.
     *
     * @param  Closure  $callback
     * @return string
     */
    protected function detectWebEnvironment(Closure $callback): string
    {
        return $callback();
    }

    /**
     * Set the application environment from command-line arguments.
     *
     * @param Closure $callback
     * @param array $args
     * @return string|null
     */
    protected function detectConsoleEnvironment(Closure $callback, array $args): ?string
    {
        // First we will check if an environment argument was passed via console arguments
        // and if it was that automatically overrides as the environment. Otherwise, we
        // will check the environment as a "web" request like a typical HTTP request.
        if (! is_null($value = $this->getEnvironmentArgument($args))) {
            return $value;
        }

        return $this->detectWebEnvironment($callback);
    }

    /**
     * Get the environment argument from the console.
     *
     * @param  array  $args
     * @return string|null
     */
    protected function getEnvironmentArgument(array $args): ?string
    {
        foreach ($args as $i => $value) {
            if ($value === '--env') {
                return $args[$i + 1] ?? null;
            }

            if (Str::startsWith($value, '--env')) {
                $array = array_slice(Str::split($value, '='), 1);
                return reset($array);
            }
        }

        return null;
    }
}