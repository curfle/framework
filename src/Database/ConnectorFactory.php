<?php

namespace Curfle\Database;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Support\Exceptions\Database\DriverUnknownException;

class ConnectorFactory
{
    /**
     * Creates a Connector based on a config.
     *
     * @param array $config
     * @return SQLConnectorInterface
     * @throws DriverUnknownException
     */
    public static function fromConfig(array $config) : SQLConnectorInterface
    {
        if (($config["driver"] ?? null) === "mysql") {
            return new MySQLConnector(
                $config["host"] ?? null,
                $config["username"] ?? null,
                $config["password"] ?? null,
                $config["database"] ?? null,
                $config["port"] ?? null,
                $config["socket"] ?? null,
                $config["charset"] ?? null
            );
        } else if (($config["driver"] ?? null) === "sqlite") {
            return new SQLiteConnector(
                $config["database"] ?? null,
                $config["foreign_key_constraints"] ?? null,
            );
        }else{
            throw new DriverUnknownException("The driver [{$config["driver"]}] is not supported.");
        }
    }
}