<?php

namespace Curfle\DAO\Relationships;

class RelationshipCache
{

    /**
     * Holds the cached results of a relation.
     *
     * @var array
     */
    public array $cache = [];

    /**
     * Checks if data are available for an identifier.
     *
     * @param string $identifier
     * @return bool
     */
    public function inCache(string $identifier): bool
    {
        return array_key_exists($identifier, $this->cache);
    }

    /**
     * Sets data in the cache.
     *
     * @param string $identifier
     * @param mixed $data
     * @return $this
     */
    public function cache(string $identifier, mixed $data): static
    {
        $this->cache[$identifier] = $data;
        return $this;
    }

    /**
     * Returns data from the cache.
     *
     * @param string $identifier
     * @return mixed
     */
    public function get(string $identifier): mixed
    {
        return $this->cache[$identifier];
    }
}