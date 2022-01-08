<?php

namespace Curfle\Agreements\Database\Schema\Grammar;


use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Schema\Blueprint;

interface Grammar
{

    /**
     * Turns the table blueprint into a prepared create-statement in the connector.
     *
     * @param string $name
     * @param Blueprint $blueprint
     * @param SQLConnectorInterface $connector
     * @return string
     */
    public function compileCreateBlueprint(string $name, Blueprint $blueprint, SQLConnectorInterface $connector): string;

    /**
     * Turns the table blueprint into a prepared alter statement in the connector.
     *
     * @param string $name
     * @param Blueprint $blueprint
     * @param SQLConnectorInterface $connector
     * @return string
     */
    public function compileAlterBlueprint(string $name, Blueprint $blueprint, SQLConnectorInterface $connector): string;
}