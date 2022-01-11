<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeMigrationCommand extends MakeCommand
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:migration {name}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Creates a new migration class.";

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle(Input $input) {
        // get name and create file
        $name = $input->argument("name");
        $filename = $this->app->basePath("database/migrations/") . $this->createFileName($name, true);
        $this->makeFile(
            $name,
            $filename
        );
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Migration.template";
    }
}