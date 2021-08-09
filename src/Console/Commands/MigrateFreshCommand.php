<?php

namespace Curfle\Console\Commands;

use Closure;
use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateFreshCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("migrate:fresh")
            ->description("Rolls back all migration that were run and runs all migrations that are available")
            ->resolver(function (Application $app, FileSystem $files) {
                $migrator = new Migrator($app, $files);

                // roll back migrations
                $migrationsRun = $migrator->rollback();

                // send feedback to the user
                if(empty($migrationsRun)){
                    $this->warning("no migrations were rolled back");
                }else{
                    $this->write("migrations rolled back:");
                    foreach ($migrationsRun as $migration) {
                        $this->write("- $migration");
                    }
                    $this->success("successfully rolled back all migrations");
                }

                // run migrations
                $migrationsRun = $migrator->run();

                // send feedback to the user
                if(empty($migrationsRun)){
                    $this->warning("no migrations were run");
                }else{
                    $this->write("migrations run:");
                    foreach ($migrationsRun as $migration) {
                        $this->write("- $migration");
                    }
                    $this->success("successfully run all migrations");
                }
            });
    }
}