<?php

namespace Curfle\DAO;

use Curfle\DAO\Relationships\RelationshipCache;
use Curfle\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRelationshipCache();
    }

    /**
     * Register the relationship cache implementation.
     *
     * @return void
     */
    protected function registerRelationshipCache()
    {
        $this->app->singleton("relationshipcache", function () {
            return new RelationshipCache();
        });
    }
}