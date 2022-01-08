<?php

namespace Curfle\Database\Connectors;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Agreements\Database\Schema\BuilderInterface;
use Curfle\Database\Queries\MySQLQuery;
use Curfle\Database\Schema\MySQLSchemaBuilder;
use Curfle\Support\Arr;
use Curfle\Support\Exceptions\Database\ConnectionFailedException;
use Curfle\Support\Exceptions\Database\SQLException;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Support\Str;
use Exception;
use mysqli;
use mysqli_sql_exception;
use mysqli_stmt;
use mysqli_result;

/**
 * Class MySQL
 * @package DAOInterface\Connectors
 */
class MySQLConnector implements SQLConnectorInterface
{
    /**
     * MySQLi connection.
     *
     * @var mysqli|null
     */
    private mysqli|null $connection = null;

    /**
     * MySQLi statement.
     *
     * @var mysqli_stmt|null
     */
    private mysqli_stmt|null $stmt = null;


    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param string|null $port
     * @param string|null $socket
     * @param string|null $charset
     */
    public function __construct(
        public string  $host,
        public string  $user,
        public string  $password,
        public string  $database,
        public ?string $port = null,
        public ?string $socket = null,
        public ?string $charset = null
    )
    {
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     */
    function connect(): mysqli
    {
        if ($this->connection === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            try {
                $this->connection = new mysqli(
                    $this->host,
                    $this->user,
                    $this->password,
                    $this->database,
                    $this->port,
                    $this->socket,
                );
            } catch (Exception $e) {
                throw new ConnectionFailedException($e->getMessage());
            }


            if ($this->charset !== null)
                $this->connection->set_charset($this->charset);
        }

        return $this->connection;
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     */
    function disconnect(): void
    {
        if ($this->connection !== null) {
            $this->connect()->kill($this->connect()->thread_id);
            $this->connect()->close();
        }
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     * @throws SQLException
     * @throws LogicException
     */
    function query(string $query = null): bool|mysqli_result
    {
        try {
            if ($query === null) {
                if ($this->stmt === null) {
                    throw new LogicException("Cannot execute [null] statement. No prepared statement was provided.");
                }
                $this->stmt->execute();
                return $this->stmt->get_result();
            } else {
                return $this->connect()->query($query);
            }
        } catch (mysqli_sql_exception $e) {
            throw new SQLException($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     * @throws SQLException
     * @throws LogicException|ConnectionFailedException
     */
    function execute(string $query = null): bool
    {
        try {
            if ($query === null) {
                if ($this->stmt === null) {
                    throw new LogicException("Cannot execute [null] statement. No prepared statement was provided.");
                }
                return $this->stmt->execute();
            } else {
                return (bool)$this->query($query);
            }
        } catch (mysqli_sql_exception $e) {
            throw new SQLException($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     * @throws SQLException
     * @throws LogicException|ConnectionFailedException
     */
    function rows(string $query = null): array
    {
        $result = $this->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     * @throws LogicException
     * @throws SQLException
     */
    function row(string $query = null): ?array
    {
        return $this->rows($query)[0] ?? null;
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     * @throws LogicException
     * @throws SQLException
     */
    function field(string $query = null): mixed
    {
        $row = $this->row($query);
        return $row[array_key_first($row ?? [])] ?? null;
    }

    /**
     * @inheritDoc
     * @throws SQLException|ConnectionFailedException
     */
    function prepare(string $query): static
    {
        try {
            $this->stmt = $this->connect()->prepare($query);
            return $this;
        } catch (mysqli_sql_exception $e) {
            throw new SQLException($e->getMessage());
        }
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
        $values = array_map(fn($value) => is_bool($value) ? (int)$value : $value, $values);

        // auto-detect types if null
        if ($types === null) {
            $types = array_map(fn($value) => match (gettype($value)) {
                "boolean", "integer" => self::INTEGER,
                "double" => self::FLOAT,
                "string" => self::STRING,
                default => self::BLOB,
            }, $values);
        }

        // cast types to MYSQL types
        $types = array_map(fn($type) => match ($type) {
            static::INTEGER => "i",
            static::FLOAT => "d",
            static::BLOB => "b",
            default => "s",
        }, $types);

        // bind the params
        $this->stmt->bind_param(Str::concat($types, ""), ...$values);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     */
    function lastInsertedId(): int
    {
        return $this->connect()->insert_id;
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     */
    function escape(string $string): string
    {
        return $this->connect()->real_escape_string($string);
    }

    /**
     * @inheritDoc
     */
    public function table(string $table): MySQLQuery
    {
        return (new MySQLQuery($this))->table($table);
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     */
    function beginTransaction()
    {
        $this->connect()->begin_transaction();
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     */
    function commitTransaction()
    {
        $this->connect()->commit();
    }

    /**
     * @inheritDoc
     * @throws ConnectionFailedException
     */
    function rollbackTransaction()
    {
        $this->connect()->rollback();
    }

    /**
     * @inheritDoc
     */
    public function getSchemaBuilder(): BuilderInterface
    {
        return new MySQLSchemaBuilder($this);
    }
}