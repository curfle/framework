<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeMiddlewareCommand extends MakeCommand
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:middleware {name}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Creates a new middleware class.";

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle(Input $input) {
        // get name and create file
        $name = "App\\Http\\Middleware\\" . $input->argument("name");
        $filename = $this->app->basePath("app/Http/Middleware/") . $this->createFileName($name);
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
        return __DIR__ . "/../Templates/Middleware.template";
    }
}