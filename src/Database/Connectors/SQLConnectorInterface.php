<?php

namespace Curfle\Database\Connectors;

use Curfle\Database\Query\SQLQueryBuilder;

/**
 * Interface SQLConnectorInterface
 * @package Curfle\Database
 */
interface SQLConnectorInterface {

    /**
     * connects to a sql database
     * @return mixed
     */
    function connect(): mixed;

    /**
     * entry point for executing a sql query via the SQLQueryBuilder
     * @param string $table
     * @return SQLQueryBuilder
     */
    function table(string $table) : SQLQueryBuilder;

    /**
     * executes a query and returns a result
     * @param string $query
     * @return mixed
     */
    function query(string $query): mixed;

    /**
     * executes a query and returs a success indicating bool
     * @param string $query
     * @return bool
     */
    function exec(string $query): bool;

    /**
     * fetches multiple rows
     * @param string $query
     * @return array
     */
    function rows(string $query): array;

    /**
     * fetches a single row
     * @param string $query
     * @return array|null
     */
    function row(string $query): ?array;

    /**
     * returns a field value
     * @param string $query
     * @return mixed
     */
    function field(string $query): mixed;

    /**
     * returns the last inserted row id
     * @return mixed
     */
    function lastInsertedId(): mixed;

    /**
     * escapes a string
     * @param string $string
     * @return string
     */
    function escape(string $string): string;

    /**
     * begins a transaction
     */
    function beginTransaction();

    /**
     * rolls back a transaction
     */
    function rollbackTransaction();
}