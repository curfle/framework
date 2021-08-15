<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeMiddlewareCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Middleware.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:middleware {name}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->description("Creates a new middleware file")
            ->resolver(function (Input $input) {
                // get name and create file
                $name = "App\\Http\\Middleware\\" . $input->namedArgument("name");
                $filename = $this->app->basePath("app/Http/Middleware/") . $this->createFileName($name);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}