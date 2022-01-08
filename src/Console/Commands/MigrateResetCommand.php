<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateResetCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("migrate:reset")
            ->description("Resets all migrations that have been run")
            ->resolver(function (Application $app, FileSystem $files) {
                $migrator = new Migrator($app, $files);
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
            });
    }
}