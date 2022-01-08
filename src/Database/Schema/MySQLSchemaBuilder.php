<?php

namespace Curfle\Database\Schema;

use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Schema\Grammar\MySQLGrammar;

class MySQLSchemaBuilder extends Builder
{
    /**
     * @param MySQLConnector $connector
     */
    public function __construct(MySQLConnector $connector)
    {
        parent::__construct($connector);
        $this->grammar = new MySQLGrammar();
    }
}