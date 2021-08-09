<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeModelCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Model.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:model {name}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->description("Creates a new model file")
            ->resolver(function (Input $input) {
                // get name and create file
                $name = "App\Models\\" . $input->namedArgument("name");
                $filename = $this->app->basePath("app/Models/") . $this->createFileName($name);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}