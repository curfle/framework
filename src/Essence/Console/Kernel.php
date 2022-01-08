<?php

namespace Curfle\Essence\Console;

use Curfle\Agreements\Console\Kernel as KernelAgreement;
use Curfle\Console\Application as Buddy;
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
     * The buddy application.
     *
     * @var Buddy|null
     */
    protected ?Buddy $buddy = null;

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
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
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
     * Register a Closure based command with the application.
     *
     * @param Command|string $signature
     * @param callable|null $resolver
     * @return Command
     */
    public function command(Command|string $signature, callable $resolver=null): Command
    {
        if($signature instanceof Command)
            return $this->getBuddy()->add($signature);

        return $this->getBuddy()->add(
            CommandFactory::fromCallable($this->app, $signature, $resolver)
        );
    }

    public function getAllCommands(): array
    {
        return $this->getBuddy()->commands();
    }

    /**
     * @inheritDoc
     */
    public function run(Input $input): Output
    {
        try {
            return $this->runInputThroughBuddy($input);
        } catch (Throwable $e) {
            throw $e;
            $this->reportException($e);

            $this->renderException($output, $e);

            return 1;
        }
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

        return $this->getBuddy()->run($input);
    }

    /**
     * @inheritDoc
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     */
    public function bootstrap()
    {
        if (!$this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }

        if (! $this->commandsLoaded) {
            $this->commands();

            $this->commandsLoaded = true;
        }
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
    protected function bootstrappers(): array
    {
        return $this->bootstrappers;
    }

    /**
     * Get the Buddy application instance.
     *
     * @return Buddy
     */
    protected function getBuddy(): Buddy
    {
        if ($this->buddy === null)
            return $this->buddy = new Buddy($this->app);

        return $this->buddy;
    }
}