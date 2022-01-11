<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;

class MakeTestCommand extends MakeCommand
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:test {name} {kind?}";

    /**
     * The regular expressions of arguments.
     *
     * @var array|string[]
     */
    protected array $where = ["kind" => "(--unit|--integration)"];

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Creates a new test class.";

    /**
     * Execute the console command.
     * 
     * @return void
     */
    public function handle(Input $input) {
        // get name and create file
        $dir = match ($input->argument("kind")) {
            "--unit" => "unit",
            "--integration" => "integration",
            default => false
        };

        $name = "Tests\\" . ($dir ? ucwords($dir) . "\\" : "") . $input->argument("name");
        $filename = $this->app->basePath("tests/" . ($dir ? ucwords($dir) . "/" : "")) . $this->createFileName($name);
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
        return __DIR__ . "/../Templates/Test.template";
    }
}