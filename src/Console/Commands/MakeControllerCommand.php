<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeControllerCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Controller.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:controller {name}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->description("Creates a new controller file")
            ->resolver(function (Input $input) {
                // get name and create file
                $name = "App\\Http\\Controllers\\" . $input->namedArgument("name");
                $filename = $this->app->basePath("app/Http/Controllers/") . $this->createFileName($name);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}