<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeSeederCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Seeder.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:seeder {name}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->description("Creates a new seeder file")
            ->resolver(function (Input $input) {
                // get name and create file
                $name = "Database\\Seeders\\" . $input->namedArgument("name");
                $filename = $this->app->basePath("database/seeders/") . $this->createFileName($name);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}