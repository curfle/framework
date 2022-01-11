<?php

namespace Curfle\Essence\Console;

use Curfle\Agreements\Console\Kernel as KernelAgreement;
use Curfle\Console\Buddy;
use Curfle\Console\Command;
use Curfle\Console\CommandFactory;
use Curfle\Essence\Application;
use Curfle\Essence\Bootstrap\BootProviders;
use Curfle\Essence\Bootstrap\LoadConfiguration;
use Curfle\Essence\Bootstrap\LoadEnvironmentVariables;
use Curfle\Essence\Bootstrap\RegisterFacade;
use Curfle\Essence\Bootstrap\RegisterProviders;
use Curfle\Console\Input;
use Curfle\Console\Output;
use Curfle\FileSystem\FileSystem;
use Curfle\Support\Exceptions\Console\CommandNotFoundException;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use ReflectionException;
use Throwable;

class Kernel implements KernelAgreement
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * The console application.
     *
     * @var Buddy
     */
    protected Buddy $buddy;

    /**
     * The indicator if commands have been loaded.
     *
     * @var bool
     */
    private bool $commandsLoaded = false;

    /**
     * The bootstrap classes for the application.
     *
     * @var string[]
     */
    protected array $bootstrappers = [
        LoadEnvironmentVariables::class,
        LoadConfiguration::class,
        RegisterFacade::class,
        RegisterProviders::class,
        BootProviders::class
    ];

    /**
     * Create a new Console kernel instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->buddy = $app->make(Buddy::class);
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
    }

    /**
     * Load commands from a directory.
     *
     * @param string $directory
     * @return void
     */
    public function loadFromDirectory(string $directory)
    {
        $this->buddy->loadFromDirectory($directory);
    }

    /**
     * Register a Closure based command with the application.
     *
     * @param Command|string $signature
     * @param callable|null $resolver
     * @return Command
     */
    public function command(Command|string $signature, callable $resolver = null): Command
    {
        if ($signature instanceof Command)
            return $this->buddy->add($signature);

        return $this->buddy->add(
            CommandFactory::fromCallable($this->app, $signature, $resolver)
        );
    }

    /**
     * Returns all commands.
     *
     * @return array
     */
    public function getAllCommands(): array
    {
        return $this->buddy->getCommands();
    }

    /**
     * @inheritDoc
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException|CommandNotFoundException
     */
    public function run(Input $input): Output
    {
        return $this->runInputThroughBuddy($input);
    }

    /**
     * Sends the given Input through the buddy instance.
     *
     * @param Input $input
     * @return Output
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException|CommandNotFoundException
     */
    private function runInputThroughBuddy(Input $input): Output
    {
        $this->app->instance('input', $input);

        $this->bootstrap();

        return $this->buddy->run($input);
    }

    /**
     * @inheritDoc
     */
    public function bootstrap()
    {
        if (!$this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->getBootstrappers());
        }

        if (!$this->commandsLoaded) {
            $this->commands();

            $this->commandsLoaded = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function terminate(): void
    {
        $this->app->terminate();
    }

    /**
     * @inheritDoc
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function getBootstrappers(): array
    {
        return $this->bootstrappers;
    }
}