<?php

namespace Curfle\Agreements\DAO;

use Curfle\Database\Queries\Builders\SQLQueryBuilder;

interface DAOInterface {

    /**
     * Returns all available instances.
     *
     * @return array
     */
    public static function all() : array;

    /**
     * Returns an instance by its id.
     *
     * @param $id
     * @return static|object
     */
    public static function get($id) : ?static;

    /**
     * Creates a new entry in the underlying persistent data layer and returns it.
     *
     * @param array $data
     * @return ?static
     */
    public static function create(array $data) : ?static;

    /**
     * Returns the primary key value.
     *
     * @return array
     */
    public function primaryKey() : mixed;

    /**
     * Updates the current instance in the underlying persistent data layer.
     *
     * @return bool
     */
    public function update() : bool;

    /**
     * Creates a copy of the current instance in the underlying persistent data layer.
     *
     * @return bool
     */
    public function store() : bool;

    /**
     * Deletes the istance's record in the underlying persistent data layer.
     *
     * @return bool
     */
    public function delete() : bool;
}