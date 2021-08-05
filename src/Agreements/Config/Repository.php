<?php

namespace Curfle\Agreements\Config;

interface Repository
{
    /**
     * Determine if the given configuration value exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get the specified configuration value.
     *
     * @param array|string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(array|string $key, mixed $default = null): mixed;

    /**
     * Get all the configuration items for the application.
     *
     * @return array
     */
    public function all(): array;
}