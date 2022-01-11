<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateStatusCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "migrate:status";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Returns information about all migrations that were run.";

    /**
     * Execute the console command.
     */
    public function handle(Application $app, FileSystem $files) {
        $migrator = new Migrator($app, $files);
        $migrationsRun = $migrator->allMigrationsRun();
        $migrationsToRun = $migrator->allMigrationsToRun();

        // send feedback to the user
        if(empty($migrationsRun)){
            $this->warning("no migrations were run yet");
        }else{
            $this->write("migrations run:");
            foreach ($migrationsRun as $migration) {
                $this->write("- ", false)
                    ->success($migration["name"], false)
                    ->write(" at {$migration["timestamp"]} ({$migration["filename"]})}");
            }
        }

        if(empty($migrationsToRun)){
            $this->success("all available migrations were run");
        }else{
            $this->write("migrations to be run:");
            foreach ($migrationsToRun as $migration) {
                $this->write("- ", false)
                    ->warning($migration["name"], false)
                    ->write(" ({$migration["filename"]})}");
            }
        }
    }
}