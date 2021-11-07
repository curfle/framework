<?php

namespace Curfle\DAO\Relationships;

use Curfle\Agreements\DAO\DAOInterface;
use Curfle\Support\Facades\App;

abstract class Relationship
{

    /**
     * @var RelationshipCache
     */
    protected RelationshipCache $relationshipCache;

    public function __construct()
    {
        $this->relationshipCache = App::resolve("relationshipcache");
    }

    /**
     * Resolves the relationship.
     *
     * @return mixed
     */
    abstract public function get(): mixed;

    /**
     * Returns the unique key for this relationship, used for caching.
     *
     * @return string
     */
    abstract protected function getCacheKey(): string;

    /**
     * Resolves the relationship but uses cached results if available.
     *
     * @param bool $forceRefresh
     * @return mixed
     */
    public function lazy(bool $forceRefresh = false): mixed
    {
        $cacheKey = $this->getCacheKey();

        // check if cache can be used
        if (!$forceRefresh && $this->relationshipCache->inCache($cacheKey))
            return $this->relationshipCache->get($cacheKey);

        // fetch data and cache results
        $data = $this->get();
        $this->relationshipCache->cache($cacheKey, $data);
        return $data;
    }
}