<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Migrations\Migrator;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class MigrateResetCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "migrate:reset";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Resets all migrations that have been run.";

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle(Application $app, FileSystem $files) {
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
    }
}