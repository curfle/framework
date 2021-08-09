<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeMigrationCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Migration.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:migration {name}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->description("Creates a new migration file")
            ->resolver(function (Input $input) {
                // get name and create file
                $name = $input->namedArgument("name");
                $filename = $this->app->basePath("database/migrations/") . $this->createFileName($name, true);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}