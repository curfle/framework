<?php

namespace Curfle\Agreements\Database\Connectors;

use Curfle\Agreements\Database\Schema\BuilderInterface;
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
    public function connect(): mixed;

    /**
     * Disconnects from a sql database.
     *
     * @return mixed
     */
    public function disconnect(): void;

    /**
     * Entry point for executing a sql query via the SQLQueryBuilder.
     *
     * @param string $table
     * @return SQLQueryBuilder
     */
    public function table(string $table) : SQLQueryBuilder;

    /**
     * Executes a query and returns a result.
     *
     * @param string $query
     * @return mixed
     */
    public function query(string $query): mixed;

    /**
     * Executes a query and returns a success indicating bool.
     *
     * @param string $query
     * @return bool
     */
    public function exec(string $query): bool;

    /**
     * Fetches multiple rows. If no query provided, the last executed statements' result will be used.
     *
     * @param string|null $query
     * @return array
     */
    public function rows(string $query = null): array;

    /**
     * Fetches a single row. If no query provided, the last executed statements' result will be used.
     *
     * @param string|null $query
     * @return array|null
     */
    public function row(string $query = null): ?array;

    /**
     * Returns a field value. If no query provided, the last executed statements' result will be used.
     *
     * @param string|null $query
     * @return mixed
     */
    public function field(string $query = null): mixed;

    /**
     * Prepares a statement.
     *
     * @param string $query
     * @return mixed
     */
    public function prepare(string $query) : static;

    /**
     * Binds a value to a prepared statement.
     *
     * @param mixed $value
     * @param int|null $type
     * @return mixed
     */
    public function bind(mixed $value, int $type = null) : static;

    /**
     * Executes a prepared statement.
     *
     * @return mixed
     */
    public function execute() : bool;

    /**
     * Returns the last inserted row id.
     *
     * @return mixed
     */
    public function lastInsertedId(): mixed;

    /**
     * Escapes a string.
     *
     * @param string $string
     * @return string
     */
    public function escape(string $string): string;

    /**
     * Begins a transaction.
     *
     */
    public function beginTransaction();

    /**
     * Commits a transaction.
     *
     */
    public function commitTransaction();

    /**
     * Rolls back a transaction.
     *
     */
    public function rollbackTransaction();

    /**
     * Returns a new schema builder instance for the according connector.
     *
     * @return BuilderInterface
     */
    public function getSchemaBuilder() : BuilderInterface;
}