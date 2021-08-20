<?php

namespace Curfle\Database\Connectors;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Agreements\Database\Schema\BuilderInterface;
use Curfle\Database\Query\SQLQueryBuilder;
use Curfle\Database\Schema\SQLiteSchemaBuilder;
use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;
use Curfle\Support\Exceptions\Logic\LogicException;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

/**
 * Class MySQL
 * @package DAOInterface\Connectors
 */
class SQLiteConnector implements SQLConnectorInterface
{
    /**
     * SQLite3 conneciton.
     *
     * @var SQLite3|null
     */
    private SQLite3|null $connection = null;

    /**
     * SQLite3 statement.
     *
     * @var SQLite3Stmt|null
     */
    private SQLite3Stmt|null $stmt = null;

    /**
     * SQLite3 result.
     *
     * @var SQLite3Result|null
     */
    private SQLite3Result|null $result = null;

    /**
     * Internal SQLite3 bound counter for positional params.
     *
     * @var int
     */
    private int $boundCounter = 1;

    /**
     * @param string $filename
     * @param bool $foreign_keys
     * @param int|string $flags
     * @param null $encryptionKey
     */
    public function __construct(
        public string     $filename,
        public bool       $foreign_keys = false,
        public int|string $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
        public            $encryptionKey = null
    )
    {
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    function connect(): SQLite3
    {
        if ($this->connection === null) {
            if (!file_exists($this->filename))
                throw new FileNotFoundException("The given SQLite database file could not be found.");

            $this->connection = new SQLite3(
                $this->filename,
                $this->flags,
                $this->encryptionKey,
            );

            // enable errors to be reported
            $this->connection->enableExceptions(true);

            if ($this->foreign_keys === true)
                $this->connection->query("PRAGMA foreign_keys=ON");
        }

        return $this->connection;
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    function disconnect(): void
    {
        if ($this->connection !== null)
            $this->connect()->close();
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    function query(string $query): SQLite3Result
    {
        $db = $this->connect();
        return $db->query($query);
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    function exec(string $query): bool
    {
        $db = $this->connect();
        return $db->exec($query);
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     * @throws LogicException
     */
    function rows(string $query = null): array
    {
        $rows = [];
        if ($query !== null) {
            // use given query
            $db = $this->connect();
            $result = $db->query($query);
            while ($row = $result->fetchArray(SQLITE3_ASSOC))
                $rows[] = $row;
        } else {
            // use stmt
            if ($this->result === null) {
                if ($this->stmt !== null)
                    $this->execute();
                else
                    throw new LogicException("Cannot get data from [null]. No prepared statement was executed.");
            }

            while ($row = $this->result->fetchArray(SQLITE3_ASSOC))
                $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     * @throws LogicException
     */
    function row(string $query = null): ?array
    {
        return $this->rows($query)[0] ?? null;
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     * @throws LogicException
     */
    function field(string $query = null): mixed
    {
        $row = $this->row($query);
        return $row[array_key_first($row)] ?? null;
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    function prepare(string $query): static
    {
        $this->stmt = $this->connect()->prepare($query);
        $this->result = null;
        return $this;
    }

    /**
     * @inheritDoc
     */
    function bind(mixed $value, int $type = null): static
    {
        if ($this->stmt === null)
            throw new LogicException("Cannot bind value to [null]. No prepared statement found.");

        if ($type === null) {
            if (is_int($value)) $type = static::INTEGER;
            else if (is_float($value)) $type = static::FLOAT;
            else if (is_string($value)) $type = static::STRING;
            else $type = static::BLOB;
        }

        $type = match ($type) {
            static::INTEGER => SQLITE3_INTEGER,
            static::FLOAT => SQLITE3_FLOAT,
            static::BLOB => SQLITE3_BLOB,
            default => SQLITE3_TEXT,
        };

        if ($value === null)
            $type = SQLITE3_NULL;

        $this->stmt->bindParam($this->boundCounter, $value, $type);

        $this->boundCounter++;

        return $this;
    }

    /**
     * @inheritDoc
     */
    function execute(): bool
    {
        $result = $this->stmt->execute();
        $this->result = $result !== false ? $result : null;
        $this->boundCounter = 1;
        $this->stmt = null;
        return $result !== false;
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    function lastInsertedId(): int
    {
        $db = $this->connect();
        return $db->lastInsertRowID();
    }

    /**
     * @inheritDoc
     */
    function escape(string $string): string
    {
        return SQLite3::escapeString($string);
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    function beginTransaction()
    {
        $this->query("BEGIN TRANSACTION");
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    function rollbackTransaction()
    {
        $this->query("ROLLBACK");
    }

    /**
     * @inheritDoc
     */
    function table(string $table): SQLQueryBuilder
    {
        return new SQLQueryBuilder($this, $table);
    }

    /**
     * @inheritDoc
     */
    public function getSchemaBuilder(): BuilderInterface
    {
        return new SQLiteSchemaBuilder($this);
    }
}