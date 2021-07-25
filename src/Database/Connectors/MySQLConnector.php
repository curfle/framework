<?php

namespace Curfle\Database\Connectors;

use Curfle\Database\Query\SQLQueryBuilder;
use mysqli;

/**
 * Class MySQL
 * @package DAO\Connectors
 */
class MySQLConnector implements SQLConnectorInterface
{
    private mysqli|null $connection = null;
    
    public function __construct(
        public string $DB_HOST,
        public string $DB_USER,
        public string $DB_PASSWORD,
        public string $DB_DATABASE,
        public ?string $DB_PORT = null,
        public ?string $DB_SOCKET = null,
        public bool $DB_UTF8MB = false
    )
    {}

    /**
     * connects to a MySQL-DataBase.
     * Constants that need to be defined are: DB_HOST, DB_USER, DB_PASSWORD and DB_DATABASE.
     * Optional constants are DB_PORT and DB_SOCKET.
     * @return mysqli
     */
    function connect(): mysqli
    {
        if ($this->connection === null) {
            $this->connection = new mysqli(
                $this->DB_HOST,
                $this->DB_USER,
                $this->DB_PASSWORD,
                $this->DB_DATABASE,
                $this->DB_PORT,
                $this->DB_SOCKET,
            );

            if ($this->DB_UTF8MB)
                $this->connection->query("SET NAMES utf8mb4");
        }

        return $this->connection;
    }

    /**
     * executes a query and returns a result or a success indicating bool
     * @param string $query
     * @return mixed
     */
    function query(string $query): mixed
    {
        return $this->connect()->query($query);
    }

    /**
     * executes a query and returns a result or a success indicating bool (alias for query())
     * @param string $query
     * @return bool|\mysqli_result
     */
    function exec(string $query): bool
    {
        return $this->query($query);
    }

    /**
     * fetches multiple rows
     * @param string $query
     * @return array
     */
    function rows(string $query): array
    {
        return $this->connect()->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * fetches a single row
     * @param string $query
     * @return array|null
     */
    function row(string $query): ?array
    {
        return $this->rows($query)[0] ?? null;
    }

    /**
     * returns a field value
     * @param string $query
     * @return mixed
     */
    function field(string $query): mixed
    {
        $row = $this->row($query);
        return $row[array_key_first($row)] ?? null;
    }

    /**
     * returns the last inserted row id
     * @return int
     */
    function lastInsertedId(): int
    {
        return $this->connect()->insert_id;
    }

    /**
     * escapes a string
     * @param string $string
     * @return string
     */
    function escape(string $string): string
    {
        return $this->connect()->real_escape_string($string);
    }

    /**
     * begins a transaction
     */
    function beginTransaction()
    {
        $this->connect()->begin_transaction();
    }

    /**
     * rolls a transaction back
     */
    function rollbackTransaction()
    {
        $this->connect()->rollback();
    }

    /**
     * entry point for executing a sql query via the SQLQueryBuilder
     * @param string $table
     * @return SQLQueryBuilder
     */
    function table(string $table): SQLQueryBuilder
    {
        return new SQLQueryBuilder($this, $table);
    }
}