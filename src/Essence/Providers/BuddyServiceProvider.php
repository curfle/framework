<?php

namespace Curfle\Essence\Providers;

use Curfle\Console\Commands\DbCommand;
use Curfle\Console\Commands\MakeMigrationCommand;
use Curfle\Console\Commands\MigrateCommand;
use Curfle\Console\Commands\MigrateRollbackCommand;
use Curfle\Console\Commands\MigrateStatusCommand;
use Curfle\Essence\Application;
use Curfle\Console\Commands\ListCommand;
use Curfle\Http\Request;
use Curfle\Support\Facades\Buddy;
use Curfle\Support\ServiceProvider;

class BuddyServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->registerCommands();
    }

    private function registerCommands()
    {
        // db
        Buddy::command(new DbCommand($this->app));

        // list
        Buddy::command(new ListCommand($this->app));

        // make:migration
        Buddy::command(new MakeMigrationCommand($this->app));

        // migrate
        Buddy::command(new MigrateCommand($this->app));

        // migrate:rollback
        Buddy::command(new MigrateRollbackCommand($this->app));

        // migrate:status
        Buddy::command(new MigrateStatusCommand($this->app));

    }
}