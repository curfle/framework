<?php

namespace Curfle\Essence;

use Closure;
use Curfle\Config\Repository;
use Curfle\Console\Input;
use Curfle\Container\Container;
use Curfle\Filesystem\Filesystem;
use Curfle\Hash\HashManager;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Mail\MailManager;
use Curfle\Routing\Router;
use Curfle\Routing\RoutingServiceProvider;
use Curfle\Support\Arr;
use Curfle\Support\Env\Env;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Support\ServiceProvider;
use ReflectionException;
use RuntimeException;
use const PHP_SAPI;

class Application extends Container
{
    /**
     * The application's boot state.
     *
     * @var bool
     */
    private bool $booted = false;

    /**
     * The array of booting callbacks.
     *
     * @var callable[]
     */
    protected array $bootingCallbacks = [];

    /**
     * The array of booted callbacks.
     *
     * @var callable[]
     */
    protected array $bootedCallbacks = [];

    /**
     * The application's base path.
     *
     * @var string
     */
    private string $basePath = "";

    /**
     * The custom application path defined by the developer.
     *
     * @var string
     */
    protected string $appPath = "";


    /**
     * The custom database path defined by the developer.
     *
     * @var string
     */
    protected string $databasePath = "";

    /**
     * The custom storage path defined by the developer.
     *
     * @var string
     */
    protected string $storagePath = "";

    /**
     * The custom environment path defined by the developer.
     *
     * @var string
     */
    protected string $environmentPath = "";

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected bool $hasBeenBootstrapped = false;

    /**
     * The application's service providers.
     *
     * @var array
     */
    private array $serviceProviders = [];

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string
     */
    protected string $environmentFile = '.env';

    /**
     * Indicates if the application is running in the console.
     *
     * @var bool|null
     */
    protected ?bool $isRunningInConsole = null;

    /**
     * The application namespace.
     *
     * @var string|null
     */
    protected ?string $namespace = null;

    /**
     * Application constructor.
     *
     * @param string|null $basePath
     * @throws LogicException
     */
    public function __construct(string $basePath = null)
    {
        if ($basePath !== null)
            $this->setBasePath($basePath);

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * Returns whether application has been booted or not.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Boot the application.
     *
     * @return void
     */
    public function boot()
    {
        // skip if is already booted
        if ($this->isBooted())
            return;

        // boot all service providers
        array_walk($this->serviceProviders, function (ServiceProvider $provider) {
            $this->bootProvider($provider);
        });

        $this->booted = true;
    }

    /**
     * Boots a service provider
     *
     * @param ServiceProvider $provider
     * @return void
     */
    private function bootProvider(ServiceProvider $provider)
    {
        $provider->callBootingCallbacks();

        if (method_exists($provider, 'boot'))
            $this->call([$provider, 'boot']);

        $provider->callBootedCallbacks();
    }


    /**
     * Register a new boot listener.
     *
     * @param callable $callback
     * @return void
     */
    public function booting(callable $callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param callable $callback
     * @return void
     */
    public function booted(callable $callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    /**
     * Call the booting callbacks for the application.
     *
     * @param callable[] $callbacks
     * @return void
     */
    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($this);
        }
    }

    /**
     * Sets the application's base path.
     *
     * @param string $basePath
     * @return Application
     */
    public function setBasePath(string $basePath): static
    {
        $this->basePath = $basePath;

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Bind all the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $path
     * @return string
     */
    public function path(string $path = ''): string
    {
        $appPath = $this->appPath ?: $this->basePath . DIRECTORY_SEPARATOR . 'app';

        return $appPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the base path of the Curfle installation.
     *
     * @param string $path Optionally, a path to append to the base path
     * @return string
     */
    public function basePath(string $path = ""): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @param string $path Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath(string $path = ''): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'bootstrap' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param string $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath(string $path = ''): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath(): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath(): string
    {
        return $this->storagePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'storage';
    }

    /**
     * Get the path to the resources' directory.
     *
     * @param string $path
     * @return string
     */
    public function resourcePath(string $path = ''): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the views' directory.
     *
     * This method returns the first configured path in the array of view paths.
     *
     * @param string $path
     * @return string
     */
    public function viewPath(string $path = ''): string
    {
        $basePath = $this['config']->get('view.paths')[0];

        return rtrim($basePath, DIRECTORY_SEPARATOR) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the environment file directory.
     *
     * @return string
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->basePath;
    }

    /**
     * Returns the application's base path.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        // make $this available in Misc
        static::$instance = $this;

        // add app and Misc instance
        $this->instance("app", $this);
        $this->instance(Container::class, $this);
    }

    /**
     * Register all the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        // TODO: register Logger
        $this->register(RoutingServiceProvider::class);
    }

    /**
     * Run the given array of bootstrap classes.
     *
     * @param string[] $bootstrappers
     * @return void
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     */
    public function bootstrapWith(array $bootstrappers)
    {
        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
        $this->hasBeenBootstrapped = true;
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Registers a service provider in the application.
     *
     * @param ServiceProvider|string $provider
     * @param bool $force
     * @return bool|void
     */
    public function register(ServiceProvider|string $provider, bool $force = false)
    {
        // check if cached service provider can be used
        if ($registered = $this->getProvider($provider) !== null && !$force)
            return $registered;

        // if provider is just a class name -> resolve for developer
        if (is_string($provider))
            $provider = $this->resolveProvider($provider);

        // register provider
        $provider->register();

        // bind provider's binings
        foreach ($provider->bindings as $id => $resolver) {
            $this->bind($id, $resolver);
        }

        // bind provider's singletons
        foreach ($provider->singletons as $id => $resolver) {
            $this->singleton($id, $resolver);
        }

        // add service provider to service provider's list
        $this->serviceProviders[] = $provider;
    }

    /**
     * Returns a service provider of the application.
     *
     * @param ServiceProvider|string $provider
     * @return ServiceProvider|null
     */
    public function getProvider(ServiceProvider|string $provider): ?ServiceProvider
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param string|ServiceProvider $provider
     * @return array
     */
    public function getProviders(ServiceProvider|string $provider): array
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return Arr::where($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    /**
     * Creates an instance of a ServiceProvider
     * @param string $provider
     * @return ServiceProvider
     */
    public function resolveProvider(string $provider): ServiceProvider
    {
        return new $provider($this);
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     * @throws LogicException
     */
    protected function registerCoreContainerAliases()
    {
        foreach ([
                     "app" => [self::class, Container::class, Application::class],
                     "config" => [Repository::class, \Curfle\Agreements\Config\Repository::class],
                     "files" => [Filesystem::class],
                     "hash" => [HashManager::class],
                     "input" => [Input::class],
                     "mail" => [MailManager::class],
                     "request" => [Request::class],
                     "router" => [Router::class],
                     "response" => [Response::class],
                 ] as $id => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($id, $alias);
            }
        }
    }

    /**
     * Get the environment file the application is using.
     *
     * @return string
     */
    public function environmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * Detect the application's current environment.
     *
     * @param Closure $callback
     * @return string
     */
    public function detectEnvironment(Closure $callback): string
    {
        $args = $_SERVER['argv'] ?? null;

        return $this['env'] = (new EnvironmentDetector)->detect($callback, $args);
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     * @throws CircularDependencyException
     */
    public function runningInConsole(): bool
    {
        if ($this->isRunningInConsole === null)
            $this->isRunningInConsole = Env::get('APP_RUNNING_IN_CONSOLE') ?? (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');

        return $this->isRunningInConsole;
    }

    /**
     * Get the path to the database directory.
     *
     * @param string $path Optionally, a path to append to the database path
     * @return string
     */
    public function databasePath(string $path = ""): string
    {
        return ($this->databasePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'database') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Register all the configured providers.
     *
     * @return void
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws ReflectionException
     */
    public function registerConfiguredProviders()
    {
        $providers = $this->make("config")->get("app.providers");
        usort($providers, function ($provider) {
            return !str_starts_with($provider, "Curfle\\") ? 1 : -1;
        });

        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function namespace(): string
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);

        foreach ($composer["autoload"]["psr-4"] as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->path()) === realpath($this->basePath($pathChoice))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }
}