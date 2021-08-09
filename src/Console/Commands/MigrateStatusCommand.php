<?php

namespace Curfle\Console\Commands;

use Closure;
use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateStatusCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("migrate:status")
            ->description("Returns infromation about all migrations that were run")
            ->resolver(function (Application $app, FileSystem $files) {
                $migrator = new Migrator($app, $files);
                $migrationsRun = $migrator->allMigrationsRun();

                // send feedback to the user
                if(empty($migrationsRun)){
                    $this->warning("no migrations were run yet");
                }else{
                    $this->write("migrations run:");
                    foreach ($migrationsRun as $migration) {
                        $this->write("- {$migration["name"]} at {$migration["timestamp"]} ({$migration["filename"]})}");
                    }
                }
            });
    }
}