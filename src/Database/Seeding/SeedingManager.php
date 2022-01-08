<?php

namespace Curfle\Database\Seeding;

use Curfle\Essence\Application;
use Curfle\FileSystem\FileSystem;
use Curfle\Support\Exceptions\FileSystem\DirectoryNotFoundException;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Utilities\Utilities;
use ReflectionException;

class SeedingManager
{

    /**
     * The SeedingManagers' application instance.
     *
     * @var Application
     */
    private Application $app;

    /**
     * The SeedingManagers' file system instance.
     *
     * @var FileSystem
     */
    private FileSystem $files;

    /**
     * @param Application $app
     * @param FileSystem $files
     */
    public function __construct(Application $app, FileSystem $files)
    {
        $this->app = $app;
        $this->files = $files;
    }

    /**
     * Returns all seeders.
     *
     * @return array
     * @throws DirectoryNotFoundException
     */
    private function getAllSeeders(): array
    {
        $seeders = [];

        $directory = $this->app->basePath("database/seeders/");

        if (!$this->files->isDirectory($directory))
            throw new DirectoryNotFoundException("The directory [$directory] does not exist and thus cannot contain any seeders.");

        foreach ($this->files->files($directory) as $file) {
            $path = $directory . $file;
            require_once $path;
            $seeders[$file] = Utilities::getClassNameFromFile($path);
        }

        return $seeders;
    }

    /**
     * Runs all seeders that have not been run yet.
     *
     * @return array
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws DirectoryNotFoundException
     * @throws ReflectionException
     */
    public function run(): array
    {
        // get all migrations
        $seeders = $this->getAllSeeders();

        foreach ($seeders as $seeder) {
            $seeder = $this->app->build("\\Database\\Seeders\\$seeder");
            $seeder->run();
        }

        return $seeders;
    }

}