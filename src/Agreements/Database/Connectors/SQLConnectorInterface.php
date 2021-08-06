<?php

namespace Curfle\Agreements\Database\Connectors;

use Curfle\Database\Query\SQLQueryBuilder;

/**
 * Interface SQLConnectorInterface
 *
 * @package Curfle\Database
 */
interface SQLConnectorInterface {

    const INTEGER = 1;
    const FLOAT = 2;
    const STRING = 4;
    const BLOB = 8;

    /**
     * Connects to a sql database.
     *
     * @return mixed
     */
    function connect(): mixed;

    /**
     * Disconnects from a sql database.
     *
     * @return mixed
     */
    function disconnect(): void;

    /**
     * Entry point for executing a sql query via the SQLQueryBuilder.
     *
     * @param string $table
     * @return SQLQueryBuilder
     */
    function table(string $table) : SQLQueryBuilder;

    /**
     * Executes a query and returns a result.
     *
     * @param string $query
     * @return mixed
     */
    function query(string $query): mixed;

    /**
     * Executes a query and returns a success indicating bool.
     *
     * @param string $query
     * @return bool
     */
    function exec(string $query): bool;

    /**
     * Fetches multiple rows. If no query provided, the last executed statements' result will be used.
     *
     * @param string|null $query
     * @return array
     */
    function rows(string $query = null): array;

    /**
     * Fetches a single row. If no query provided, the last executed statements' result will be used.
     *
     * @param string|null $query
     * @return array|null
     */
    function row(string $query = null): ?array;

    /**
     * Returns a field value. If no query provided, the last executed statements' result will be used.
     *
     * @param string|null $query
     * @return mixed
     */
    function field(string $query = null): mixed;

    /**
     * Prepares a statement.
     *
     * @param string $query
     * @return mixed
     */
    function prepare(string $query) : static;

    /**
     * Binds a value to a prepared statement.
     *
     * @param mixed $value
     * @param int|null $type
     * @return mixed
     */
    function bind(mixed $value, int $type = null) : static;

    /**
     * Executes a prepared statement.
     *
     * @return mixed
     */
    function execute() : bool;

    /**
     * Returns the last inserted row id.
     *
     * @return mixed
     */
    function lastInsertedId(): mixed;

    /**
     * Escapes a string.
     *
     * @param string $string
     * @return string
     */
    function escape(string $string): string;

    /**
     * Begins a transaction.
     *
     */
    function beginTransaction();

    /**
     * Rolls back a transaction.
     *
     */
    function rollbackTransaction();
}