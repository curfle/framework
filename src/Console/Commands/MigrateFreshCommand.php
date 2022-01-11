<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateFreshCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "migrate:fresh";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Rolls back all migration that were run and runs all migrations that are available.";

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle(Application $app, FileSystem $files) {
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
    }
}