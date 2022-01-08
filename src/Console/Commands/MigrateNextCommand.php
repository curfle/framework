<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateNextCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("migrate:next")
            ->description("Runs the next migration that has not been run yet")
            ->resolver(function (Application $app, FileSystem $files) {
                $migrator = new Migrator($app, $files);
                $migrationsRun = $migrator->run(1);

                // send feedback to the user
                if(empty($migrationsRun)){
                    $this->warning("no migration was run");
                }else{
                    $this->write("migration run:");
                    foreach ($migrationsRun as $migration) {
                        $this->write("- $migration");
                    }
                    $this->success("successfully run the migration");
                }
            });
    }
}