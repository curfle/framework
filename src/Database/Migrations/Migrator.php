<?php

namespace Curfle\Database\Migrations;

use Curfle\Database\Schema\Blueprint;
use Curfle\Essence\Application;
use Curfle\FileSystem\FileSystem;
use Curfle\Support\Arr;
use Curfle\Support\Exceptions\FileSystem\DirectoryNotFoundException;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Support\Facades\DB;
use Curfle\Support\Facades\Schema;
use Curfle\Utilities\Utilities;
use Exception;
use ReflectionException;

class Migrator
{

    /**
     * The Migrators' application instance.
     *
     * @var Application
     */
    private Application $app;

    /**
     * The Migrators' file system instance.
     *
     * @var FileSystem
     */
    private FileSystem $files;

    /**
     * Holds all run migrations, after loading it from the database.
     *
     * @var array|null
     */
    private ?array $migrationsRun = null;

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
     * Returns the name of the migration table.
     *
     * @return string
     */
    private function getMigrationTable(): string
    {
        return $this->app->make("config")["database.migrations"];
    }

    /**
     * Returns all migrations that were run.
     *
     * @return array
     * @throws DirectoryNotFoundException
     */
    public function allMigrationsRun(): array
    {
        $this_ = $this;

        // get all migrations
        $migrations = $this->getAllMigrations();

        // filter migrations that have been run already
        $migrations = array_filter($migrations, function ($class, $filename) use ($this_) {
            return $this_->migrationAlreadyRun($filename);
        }, ARRAY_FILTER_USE_BOTH);

        return array_map(function (string $migration, string $filename) {
            return [
                "name" => $migration,
                "filename" => $filename,
                "timestamp" => DB::table($this->getMigrationTable())->where("migration", $filename)->value("timestamp")
            ];
        }, $migrations, Arr::keys($migrations));
    }

    /**
     * Returns all migrations that were not run yet.
     *
     * @return array
     * @throws DirectoryNotFoundException
     */
    public function allMigrationsToRun(): array
    {
        $this_ = $this;

        // get all migrations
        $migrations = $this->getAllMigrations();

        // filter migrations that have been run already
        $migrations = array_filter($migrations, function ($class, $filename) use ($this_) {
            return !$this_->migrationAlreadyRun($filename);
        }, ARRAY_FILTER_USE_BOTH);

        return array_map(function (string $migration, string $filename) {
            return [
                "name" => $migration,
                "filename" => $filename,
            ];
        }, $migrations, Arr::keys($migrations));
    }

    /**
     * Returns all migrations.
     *
     * @return array
     * @throws DirectoryNotFoundException
     */
    private function getAllMigrations(): array
    {
        $migrations = [];

        $directory = $this->app->basePath("database/migrations/");

        if (!$this->files->isDirectory($directory))
            throw new DirectoryNotFoundException("The directory [$directory] does not exist and thus cannot contain any migrations.");

        foreach ($this->files->files($directory) as $file) {
            $path = $directory . $file;
            require_once $path;
            $migrations[$file] = Utilities::getClassNameFromFile($path);
        }

        return $migrations;
    }

    /**
     * Creates the migration table if not exists.
     *
     * @return void
     */
    private function ensureMigrationTableExists()
    {
        $migrationTable = $this->getMigrationTable();
        if (!Schema::hasTable($migrationTable))
            Schema::create($migrationTable, function (Blueprint $table) {
                $table->id("id");
                $table->string("migration");
                $table->timestamp("timestamp");
            });
    }

    /**
     * Returns all migrations that have been run.
     *
     * @return array
     */
    private function getAllMigrationsRun(): array
    {
        if ($this->migrationsRun !== null)
            return $this->migrationsRun;

        $migrationTable = $this->getMigrationTable();

        return $this->migrationsRun = DB::table($migrationTable)->get();
    }

    /**
     * Resets all migrations that were run.
     *
     * @return void
     */
    private function resetAllMigrationsRun(): void
    {
        $this->migrationsRun = null;
    }

    /**
     * Returns wether a migration has already been run.
     *
     * @param string $identifier
     * @return bool
     */
    private function migrationAlreadyRun(string $identifier): bool
    {
        $this->ensureMigrationTableExists();

        $allRunMigrations = $this->getAllMigrationsRun();

        foreach ($allRunMigrations as $record) {
            if ($record["migration"] === $identifier)
                return true;
        }
        return false;
    }

    /**
     * Runs all migrations that have not been run yet.
     *
     * @param int|null $amount
     * @return array
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws DirectoryNotFoundException
     * @throws ReflectionException
     * @throws Exception
     */
    public function run(int $amount = null): array
    {
        $this_ = $this;

        $migrationsRun = [];

        // get all migrations
        $migrations = $this->getAllMigrations();

        $migrations = array_filter($migrations, function ($class, $filename) use ($this_) {
            return !$this_->migrationAlreadyRun($filename);
        }, ARRAY_FILTER_USE_BOTH);

        // run all migrations
        foreach ($migrations as $filename => $migration) {
            // check for amount condition
            if ($amount !== null && $amount <= 0)
                break;
            $amount--;

            // rollback the migration
            $migrationsRun[] = $migration;
            $migration = $this->app->build($migration);
            $this->runMigration($filename, $migration);
        }

        // reset cache of migrations run
        $this->resetAllMigrationsRun();

        return $migrationsRun;
    }

    /**
     * Runs a migration and takes a notice about that in the database.
     *
     * @param string $identifier
     * @param Migration $migration
     * @throws Exception
     */
    private function runMigration(string $identifier, Migration $migration)
    {
        $migration->up();

        DB::table($this->getMigrationTable())->insert([
            "migration" => $identifier,
            "timestamp" => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Rolls back all migrations that have been run.
     *
     * @param int|null $amount
     * @return array
     * @throws DirectoryNotFoundException
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws ReflectionException
     * @throws Exception
     */
    public function rollback(int $amount = null): array
    {
        $this_ = $this;

        $migrationsRolledBack = [];

        // get all migrations
        $migrations = $this->getAllMigrations();

        // filter migrations that have been run already
        $migrations = array_filter($migrations, function ($class, $filename) use ($this_) {
            return $this_->migrationAlreadyRun($filename);
        }, ARRAY_FILTER_USE_BOTH);

        // turn around order
        $migrations = array_reverse($migrations);

        // roll back all migrations
        foreach ($migrations as $filename => $migration) {
            // check for amount condition
            if ($amount !== null && $amount <= 0)
                break;
            $amount--;

            // rollback the migration
            $migrationsRolledBack[] = $migration;
            $migration = $this->app->build($migration);
            $this->rollbackMigration($filename, $migration);
        }

        // reset cache of migrations run
        $this->resetAllMigrationsRun();

        return $migrationsRolledBack;
    }

    /**
     * Rolls back a migration and takes a notice about that in the database.
     *
     * @param string $identifier
     * @param Migration $migration
     * @throws Exception
     */
    private function rollbackMigration(string $identifier, Migration $migration)
    {
        $migration->down();

        DB::table($this->getMigrationTable())->where("migration", $identifier)->delete();
    }
}