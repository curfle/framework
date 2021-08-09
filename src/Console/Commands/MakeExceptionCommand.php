<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeExceptionCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Exception.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:exception {name}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->description("Creates a new exception file")
            ->resolver(function (Input $input) {
                // get name and create file
                $name = "App\Exceptions\\" . $input->namedArgument("name");
                $filename = $this->app->basePath("app/Exceptions/") . $this->createFileName($name);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}