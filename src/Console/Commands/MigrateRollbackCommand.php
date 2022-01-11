<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateRollbackCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "migrate:rollback";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Rolls back the last migration that has been run.";

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle(Application $app, FileSystem $files) {
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
    }
}