<?php

namespace Curfle\Console\Commands;

use Closure;
use Curfle\Database\Migrations\Migrator;
use Curfle\Database\Seeding\SeedingManager;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;

class DbSeedCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("db:seed")
            ->description("Seeds the database")
            ->resolver(function (Application $app, FileSystem $files) {
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

            });
    }
}