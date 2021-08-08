<?php

namespace Curfle\Essence\Providers;

use Curfle\Console\Commands\DbCommand;
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
        // list
        Buddy::command(new ListCommand($this->app));

        // db
        Buddy::command(new DbCommand($this->app));

    }
}