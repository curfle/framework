<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeMailCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Mailable.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:mail {name}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->description("Creates a new mail file")
            ->resolver(function (Input $input) {
                // get name and create file
                $name = "App\\Mail\\" . $input->namedArgument("name");
                $filename = $this->app->basePath("app/Mail/") . $this->createFileName($name);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}