<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeTestCommand extends MakeCommand
{

    /**
     * @inheritDoc
     */
    protected function getTemplate(): string
    {
        return __DIR__ . "/../Templates/Test.template";
    }

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:test {name} {kind?}")
            ->where("name", "([a-z]|[A-Z])+([a-z]|[A-Z]|[0-9])*")
            ->where("kind", "(--unit|--integration)")
            ->description("Creates a new test file")
            ->resolver(function (Input $input) {
                // get name and create file
                $dir = match ($input->namedArgument("kind")) {
                    "--unit" => "unit",
                    "--integration" => "integration",
                    default => false
                };

                $name = "Tests\\" . ($dir ? ucwords($dir) . "\\" : "") . $input->namedArgument("name");
                $filename = $this->app->basePath("tests/" . ($dir ? ucwords($dir) . "/" : "")) . $this->createFileName($name);
                $this->makeFile(
                    $name,
                    $filename
                );
            });
    }
}