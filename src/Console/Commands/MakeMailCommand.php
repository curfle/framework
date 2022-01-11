<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeMailCommand extends MakeCommand
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:mail {name}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Creates a new mail class.";

    /**
     * Execute the console command.
     */
    public function handle(Input $input) {
        // get name and create file
        $name = "App\\Mail\\" . $input->argument("name");
        $filename = $this->app->basePath("app/Mail/") . $this->createFileName($name);
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
        return __DIR__ . "/../Templates/Mailable.template";
    }
}