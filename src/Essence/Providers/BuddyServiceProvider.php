<?php

namespace Curfle\Essence\Providers;

use Curfle\Agreements\Console\Kernel;
use Curfle\Console\Buddy;
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
use Curfle\Essence\Application;
use Curfle\FileSystem\FileSystem;
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

    /**
     * Registers all built-in commands.
     *
     * @return void
     */
    private function registerCommands() {
        $this->app
            ->make(Kernel::class)
            ->loadFromDirectory(__DIR__ . "/../../Console/Commands");
    }
}