<?php

namespace Curfle\Database\Queries;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Queries\Builders\MySQLQueryBuilder;

class MySQLQuery extends Query
{
    public function __construct(SQLConnectorInterface $connector)
    {
        parent::__construct($connector);
        $this->builder = new MySQLQueryBuilder();
    }
}