<?php

namespace Curfle\Console\Commands;

use Curfle\Database\Seeding\SeedingManager;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class DbSeedCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "db:seed";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Seeds the database.";

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle(Application $app, FileSystem $files)
    {
        $manager = new SeedingManager($app, $files);
        $seedersRun = $manager->run();

        // send feedback to the user
        if (empty($seedersRun)) {
            $this->warning("no seeders were run");
        } else {
            $this->write("seeders run:");
            foreach ($seedersRun as $seeder) {
                $this->write("- " . $files->basename($seeder));
            }
            $this->success("successfully run all seeders");
        }
    }
}