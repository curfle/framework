<?php

namespace Curfle\Database\Connectors;

use Curfle\Contracts\FileSystem\FileNotFoundException;
use Curfle\Database\Query\SQLQueryBuilder;
use Curfle\FileSystem\FileSystem;
use SQLite3;
use SQLite3Result;

/**
 * Class MySQL
 * @package DAO\Connectors
 */
class SQLiteConnector implements SQLConnectorInterface
{
    private SQLite3|null $connection = null;

    public function __construct(
        public string $DB_FILENAME,
        public bool $DB_FOREIGN_KEYS = false,
        public $DB_FLAGS = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
        public $DB_ENCRYPTION_KEY = null
    )
    {
    }

    /**
     * connects to a SQLite-DataBase.
     * Constants that need to be defined are: DB_FILENAME.
     * Optional constants are DB_FLAGS and DB_ENCRYPTION_KEY.
     * @return SQLite3
     * @throws FileNotFoundException
     */
    function connect(): SQLite3
    {
        if ($this->connection === null) {
            if(FileSystem::missing($this->DB_FILENAME))
                throw new FileNotFoundException("The given SQLite database file could not be found.");

            $this->connection = new SQLite3(
                $this->DB_FILENAME,
                $this->DB_FLAGS,
                $this->DB_ENCRYPTION_KEY,
            );

            if ($this->DB_FOREIGN_KEYS === true)
                $this->connection->query("PRAGMA foreign_keys=ON");
        }

        return $this->connection;
    }

    /**
     * executes a query and returns a result
     * @param string $query
     * @return SQLite3Result
     */
    function query(string $query): SQLite3Result
    {
        $db = $this->connect();
        return $db->query($query);
    }

    /**
     * executes a query and returs a success indicating bool
     * @param string $query
     * @return bool
     */
    function exec(string $query): bool
    {
        $db = $this->connect();
        return $db->exec($query);
    }

    /**
     * fetches multiple rows
     * @param string $query
     * @return array
     */
    function rows(string $query): array
    {
        $db = $this->connect();
        $res = $db->query($query);
        $rows = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC))
            $rows[] = $row;
        return $rows;
    }

    /**
     * fetches a single row
     * @param string $query
     * @return array
     */
    function row(string $query): array
    {
        return $this->rows($query)[0];
    }

    /**
     * returns a field value
     * @param string $query
     * @return mixed
     */
    function field(string $query): mixed
    {
        $db = $this->connect();
        return $db->querySingle($query);
    }

    /**
     * returns the last inserted row id
     * @return int
     */
    function lastInsertedId(): int
    {
        $db = $this->connect();
        return $db->lastInsertRowID();
    }

    /**
     * escapes a string
     * @param string $string
     * @return string
     */
    function escape(string $string): string
    {
        return SQLite3::escapeString($string);
    }

    /**
     * begins a transaction
     */
    function beginTransaction()
    {
        $this->query("BEGIN TRANSACTION");
    }

    /**
     * rolls a transaction back
     */
    function rollbackTransaction()
    {
        $this->query("ROLLBACK");
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