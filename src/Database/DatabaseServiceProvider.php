<?php

namespace Curfle\Database;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\DAO\Model;
use Curfle\Essence\Application;
use Curfle\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function boot(){
        Model::setConnector(get_class($this->app->resolve("db.connection")));
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->app->singleton('db', function (Application $app) {
            return new DatabaseManager($app);
        });

        $this->app->bind('db.connection', function (Application $app) {
            return $app['db']->connector();
        });
    }
}