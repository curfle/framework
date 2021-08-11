<?php

namespace Curfle\DAO\Relationships;

use Curfle\Agreements\DAO\DAOInterface;

abstract class Relationship
{

    /**
     * Resolves the relationship.
     *
     * @return mixed
     */
    abstract function get() : mixed;
}