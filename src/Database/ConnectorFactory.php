<?php

namespace Curfle\Database;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Support\Exceptions\DatabaseConnectionNotFoundException;
use Curfle\Support\Exceptions\DatabaseDriverUnknownException;

class ConnectorFactory
{
    /**
     * Creates a Connector based on a config.
     *
     * @param array $config
     * @return SQLConnectorInterface
     * @throws DatabaseDriverUnknownException
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
            throw new DatabaseDriverUnknownException("The driver [{$config["driver"]}] is not supported.");
        }
    }
}