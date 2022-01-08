<?php

namespace Curfle\Essence\Providers;

use Curfle\Console\Commands\DbCommand;
use Curfle\Console\Commands\DbSeedCommand;
use Curfle\Console\Commands\MakeControllerCommand;
use Curfle\Console\Commands\MakeExceptionCommand;
use Curfle\Console\Commands\MakeGuardianCommand;
use Curfle\Console\Commands\MakeMailCommand;
use Curfle\Console\Commands\MakeMiddlewareCommand;
use Curfle\Console\Commands\MakeMigrationCommand;
use Curfle\Console\Commands\MakeModelCommand;
use Curfle\Console\Commands\MakeSecretCommand;
use Curfle\Console\Commands\MakeSeederCommand;
use Curfle\Console\Commands\MakeTestCommand;
use Curfle\Console\Commands\MigrateCommand;
use Curfle\Console\Commands\MigrateFreshCommand;
use Curfle\Console\Commands\MigrateNextCommand;
use Curfle\Console\Commands\MigrateResetCommand;
use Curfle\Console\Commands\MigrateRollbackCommand;
use Curfle\Console\Commands\MigrateStatusCommand;
use Curfle\Console\Commands\ServeCommand;
use Curfle\Console\Commands\ListCommand;
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

        // db:seed
        Buddy::command(new DbSeedCommand($this->app));

        // list
        Buddy::command(new ListCommand($this->app));

        // make:controller
        Buddy::command(new MakeControllerCommand($this->app));

        // make:exception
        Buddy::command(new MakeExceptionCommand($this->app));

        // make:guardian
        Buddy::command(new MakeGuardianCommand($this->app));

        // make:mail
        Buddy::command(new MakeMailCommand($this->app));

        // make:middleware
        Buddy::command(new MakeMiddlewareCommand($this->app));

        // make:migration
        Buddy::command(new MakeMigrationCommand($this->app));

        // make:model
        Buddy::command(new MakeModelCommand($this->app));

        // make:secret
        Buddy::command(new MakeSecretCommand($this->app));

        // make:seeder
        Buddy::command(new MakeSeederCommand($this->app));

        // make:test
        Buddy::command(new MakeTestCommand($this->app));

        // migrate
        Buddy::command(new MigrateCommand($this->app));

        // migrate:fresh
        Buddy::command(new MigrateFreshCommand($this->app));

        // migrate:next
        Buddy::command(new MigrateNextCommand($this->app));

        // migrate:reset
        Buddy::command(new MigrateResetCommand($this->app));

        // migrate:rollback
        Buddy::command(new MigrateRollbackCommand($this->app));

        // migrate:status
        Buddy::command(new MigrateStatusCommand($this->app));

        // serve
        Buddy::command(new ServeCommand($this->app));

    }
}