<?php

namespace Curfle\Database\Connectors;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Agreements\Database\Schema\BuilderInterface;
use Curfle\Database\Queries\SQLiteQuery;
use Curfle\Database\Schema\SQLiteSchemaBuilder;
use Curfle\Support\Arr;
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
                ...$this->encryptionKey !== null
                ? [$this->filename, $this->flags, $this->encryptionKey]
                : [$this->filename, $this->flags,]
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
     * @throws LogicException
     */
    function query(string $query = null): SQLite3Result
    {
        if ($query === null) {
            if ($this->stmt === null) {
                throw new LogicException("Cannot execute [null] statement. No prepared statement was provided.");
            }
            return $this->stmt->execute();
        } else {
            return $this->connect()->query($query);
        }
    }

    /**
     * @inheritDoc
     * @throws LogicException|FileNotFoundException
     */
    function execute(string $query = null): bool
    {
        if ($query === null) {
            if ($this->stmt === null) {
                throw new LogicException("Cannot execute [null] statement. No prepared statement was provided.");
            }
            return $this->stmt->execute() !== false;
        } else {
            return $this->connect()->exec($query);
        }
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException|LogicException
     */
    function rows(string $query = null): array
    {
        $rows = [];
        $result = $this->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException|LogicException
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
        $this->stmttxt = $query;
        $this->stmt = $this->connect()->prepare($query);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    function bind(mixed $values, int|array $types = null): static
    {

        // check if statement is prepared
        if ($this->stmt === null)
            throw new LogicException("Cannot bind value to [null]. No prepared statement found.");

        // cast $types to array if not null
        if ($types !== null && !Arr::is($types))
            $types = [$types];

        // cast $values to array
        if (!Arr::is($values))
            $values = [$values];


        // check if number of types equals number of values
        if ($types !== null && Arr::length($values) !== Arr::length($types))
            throw new LogicException("The number of values passed as parameter must equal the number of types.");

        // cast bools to ints
        array_walk($values, fn($value) => is_bool($value) ? (int)$value : $value);

        // auto-detect types if null
        if ($types === null) {
            $types = array_map(fn($value) => match (gettype($value)) {
                "boolean", "integer" => self::INTEGER,
                "double" => self::FLOAT,
                "string" => self::STRING,
                default => self::BLOB,
            }, $values);
        }

        // cast types to SQLITE types
        $types = array_map(fn($type) => match ($type) {
            static::INTEGER => SQLITE3_INTEGER,
            static::FLOAT => SQLITE3_FLOAT,
            static::BLOB => SQLITE3_BLOB,
            default => SQLITE3_TEXT,
        }, $types);

        // check for null type
        for ($i = 0; $i < Arr::length($values); $i++) {
            if ($values[$i] === null) {
                $types[$i] = SQLITE3_NULL;
            }
        }

        // bind params by position
        for ($i = 0; $i < Arr::length($values); $i++) {
            $this->stmt->bindParam($i + 1, $values[$i], $types[$i]);
        }

        return $this;
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
     */
    public function table(string $table): SQLiteQuery
    {
        return (new SQLiteQuery($this))->table($table);
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     * @throws LogicException
     */
    function beginTransaction()
    {
        $this->query("BEGIN TRANSACTION");
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     * @throws LogicException
     */
    function commitTransaction()
    {
        $this->query("COMMIT");
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     * @throws LogicException
     */
    function rollbackTransaction()
    {
        $this->query("ROLLBACK");
    }

    /**
     * @inheritDoc
     */
    public function getSchemaBuilder(): BuilderInterface
    {
        return new SQLiteSchemaBuilder($this);
    }
}