<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeSeederCommand extends MakeCommand
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:seeder {name}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Creates a new seeder class.";

    /**
     * Execute the console command.
     */
    public function handle(Input $input) {
        // get name and create file
        $name = "Database\\Seeders\\" . $input->argument("name");
        $filename = $this->app->basePath("database/seeders/") . $this->createFileName($name);
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
        return __DIR__ . "/../Templates/Seeder.template";
    }
}