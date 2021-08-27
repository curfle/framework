<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeGuardianCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Guardian.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:guardian {name}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->description("Creates a new guardian file")
            ->resolver(function (Input $input) {
                // get name and create file
                $name = "App\\Auth\\Guardians\\" . $input->namedArgument("name");
                $filename = $this->app->basePath("app/Auth/Guardians/") . $this->createFileName($name);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}