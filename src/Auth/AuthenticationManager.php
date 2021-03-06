<?php

namespace Curfle\Auth;

use Curfle\Agreements\Auth\Guardian;
use Curfle\Essence\Application;
use Curfle\Support\Exceptions\Auth\GuardianNotFoundException;
use Curfle\Support\Exceptions\Auth\ProvidedGuardianNotGuardianInstanceException;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use ReflectionException;

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
     * @throws ProvidedGuardianNotGuardianInstanceException
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
     * @throws ProvidedGuardianNotGuardianInstanceException
     */
    private function loadGuardians()
    {
        $guardians = $this->app->make("config")["auth.guardians"];

        foreach ($guardians as $name => $guardian) {
            // create instance of guardian
            $classname = $guardian["guardian"];
            $instance = $this->app->make($classname);

            if (!$instance instanceof Guardian)
                throw new ProvidedGuardianNotGuardianInstanceException("The created instance of the provided guardian classname [$classname] is not an instance of Curfle\Agreements\Auth\Guardian.");

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
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws GuardianNotFoundException
     * @throws ProvidedGuardianNotGuardianInstanceException
     */
    public function __call(string $method, array $parameters)
    {
        return $this->guardian()->$method(...$parameters);
    }
}