<?php

namespace Curfle\Auth;

use Curfle\Agreements\Auth\Guardian;
use Curfle\Essence\Application;
use Curfle\Support\Exceptions\Auth\GuardianNotFoundException;
use Curfle\Support\Exceptions\Auth\ProvidedGuardianNotGuardianInstance;

class AuthenticationManager
{

    /**
     * Application instance.
     *
     * @var Application
     */
    private Application $app;

    /**
     * All stored connectors.
     *
     * @var Guardian[]
     */
    private ?array $guardians = null;

    /**
     * All authenticated users.
     *
     * @var Authenticatable[]
     */
    private array $authenticatedUsers = [];

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Returns a Guardian.
     *
     * @param string|null $name
     * @return Guardian
     * @throws ProvidedGuardianNotGuardianInstance
     * @throws GuardianNotFoundException
     */
    public function guardian(string $name = null): Guardian
    {
        if($this->guardians === null)
            $this->loadGuardians();

        // get guardian
        $guardian = $this->guardians[$name ?? "default"];
        if ($guardian === null)
            throw new GuardianNotFoundException("The guardian [$name] could not be found");

        return $guardian;
    }

    /**
     * @throws ProvidedGuardianNotGuardianInstance
     */
    private function loadGuardians()
    {
        $guardians = $this->app["config"]["auth.guardians"];

        foreach ($guardians as $name => $guardian) {
            // create instance of guardian
            $classname = $guardian["guardian"];
            $instance = $this->app->resolve($classname);

            if (!$instance instanceof Guardian)
                throw new ProvidedGuardianNotGuardianInstance("The created instance of the provided guardian classname [$classname] is not an instance of Curfle\Agreements\Auth\Guardian.");

            // add drivers to guardian
            foreach ($guardian["drivers"] as $driver)
                $instance->addDriver($driver);

            // set authenticatable of guardian
            $instance->setAuthenticatable($guardian["authenticatable"] ?? null);

            // register the guard
            $this->registerGuardian($name, $instance);
        }
    }

    /**
     * Registers a guardian.
     *
     * @param string $name
     * @param Guardian $guardian
     */
    private function registerGuardian(string $name, Guardian $guardian)
    {
        $this->guardians[$name] = $guardian;
    }

    /**
     * Adds an authenticated user.
     *
     * @param Authenticatable $user
     * @param string $name
     * @return $this
     */
    public function login(Authenticatable $user, string $name = "default") : static
    {
        $this->authenticatedUsers[$name] = $user;
        return $this;
    }

    /**
     * Returns an authenticated user.
     *
     * @param string $name
     * @return ?Authenticatable
     */
    public function user(string $name = "default") : ?Authenticatable
    {
        return $this->authenticatedUsers[$name] ?? null;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws GuardianNotFoundException
     * @throws ProvidedGuardianNotGuardianInstance
     */
    public function __call(string $method, array $parameters)
    {
        return $this->guardian()->$method(...$parameters);
    }
}