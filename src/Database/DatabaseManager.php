<?php

namespace Curfle\Database;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Essence\Application;
use Curfle\Support\Exceptions\Database\ConnectionNotFoundException;
use Curfle\Support\Exceptions\Database\DriverUnknownException;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use ReflectionException;

class DatabaseManager
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
     * @var SQLConnectorInterface[]
     */
    private array $connectors;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the default connection name.
     *
     * @return string
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws ReflectionException
     */
    public function getDefaultConnectionName(): string
    {
        return $this->app->resolve("config")["database.default"];
    }

    /**
     * Sets the default connection name.
     *
     * @param string $name
     * @return void
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws ReflectionException
     */
    public function setDefaultConnectionName(string $name)
    {
        $this->app->resolve("config")["database.default"] = $name;
    }

    /**
     * Returns a SQL connector.
     *
     * @param string|null $name
     * @return SQLConnectorInterface
     * @throws ConnectionNotFoundException
     * @throws DriverUnknownException
     */
    public function connector(string $name = null): SQLConnectorInterface
    {

        $name = $name ?? $this->getDefaultConnectionName();

        if (!isset($this->connectors[$name]))
            $this->connectors[$name] = $this->createConnector($name);

        return $this->connectors[$name];
    }

    /**
     * Creates a new connector based on a config name.
     *
     * @param string $name
     * @return SQLConnectorInterface
     * @throws ConnectionNotFoundException
     * @throws DriverUnknownException
     */
    private function createConnector(string $name): SQLConnectorInterface
    {
        $possibleConnections = $this->app->resolve("config")["database.connections"];
        if (!isset($possibleConnections[$name]))
            throw new ConnectionNotFoundException("The connection [$name] could not be found in the projects configuration.");

        return ConnectorFactory::fromConfig($possibleConnections[$name]);
    }

    /**
     * Disconnects all connectors.
     *
     * @return void
     */
    public function disconnectConnectors()
    {
        foreach ($this->connectors as $connector)
            $connector->disconnect();
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws ConnectionNotFoundException
     * @throws DriverUnknownException
     */
    public function __call(string $method, array $parameters)
    {
        return $this->connector()->$method(...$parameters);
    }
}