<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeExceptionCommand extends MakeCommand
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:exception {name}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Creates a new exception class.";

    /**
     * Execute the console command.
     */
    public function handle(Input $input) {
        // get name and create file
        $name = "App\Exceptions\\" . $input->argument("name");
        $filename = $this->app->basePath("app/Exceptions/") . $this->createFileName($name);
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
        return __DIR__ . "/../Templates/Exception.template";
    }
}