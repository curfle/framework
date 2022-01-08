<?php

namespace Curfle\Agreements\Support\Env;

interface Repository
{
    /**
     * Determine if the given environment variable is defined.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Get an environment variable.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function get(string $name): ?string;

    /**
     * Set an environment variable.
     *
     * @param string $name
     * @param string $value
     *
     * @return bool
     */
    public function set(string $name, string $value): bool;
}