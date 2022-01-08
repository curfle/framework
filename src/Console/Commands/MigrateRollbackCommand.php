<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateRollbackCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("migrate:rollback")
            ->description("Rolls back the last migration that has been run")
            ->resolver(function (Application $app, FileSystem $files) {
                $migrator = new Migrator($app, $files);
                $migrationsRun = $migrator->rollback(1);

                // send feedback to the user
                if(empty($migrationsRun)){
                    $this->warning("no migration was rolled back");
                }else{
                    $this->write("migration rolled back:");
                    foreach ($migrationsRun as $migration) {
                        $this->write("- $migration");
                    }
                    $this->success("successfully rolled back the migration");
                }
            });
    }
}