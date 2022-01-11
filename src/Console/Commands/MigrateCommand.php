<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "migrate";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Runs all migrations that have not been run yet.";

    /**
     * Execute the console command.
     */
    public function handle(Application $app, FileSystem $files) {
        $migrator = new Migrator($app, $files);
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