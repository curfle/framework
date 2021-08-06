<?php

namespace Curfle\Database\Connectors;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Query\SQLQueryBuilder;
use Curfle\Support\Exceptions\LogicException;
use mysqli;
use mysqli_stmt;
use mysqli_result;
use function React\Promise\map;

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
     * MySQLi statement result.
     *
     * @var mysqli_result|null
     */
    private mysqli_result|null $result = null;

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
     */
    function connect(): mysqli
    {
        if ($this->connection === null) {
            $this->connection = new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->database,
                $this->port,
                $this->socket,
            );

            if ($this->charset !== null)
                $this->connection->set_charset($this->charset);
        }

        return $this->connection;
    }

    /**
     * @inheritDoc
     */
    function disconnect(): void
    {
        if($this->connection !== null) {
            $this->connect()->kill($this->connect()->thread_id);
            $this->connect()->close();
        }
    }

    /**
     * @inheritDoc
     */
    function query(string $query): mixed
    {
        return $this->connect()->query($query);
    }

    /**
     * @inheritDoc
     */
    function exec(string $query): bool
    {
        return $this->query($query);
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    function rows(string $query = null): array
    {
        if ($query !== null) {
            // use given query
            return $this->connect()->query($query)->fetch_all(MYSQLI_ASSOC);
        } else {
            // use stmt
            if ($this->result === null){
                if($this->stmt !== null)
                    $this->execute();
                else
                    throw new LogicException("Cannot get data from [null]. No prepared statement was executed.");
            }

            return $this->result->fetch_all(MYSQLI_ASSOC);
        }
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    function row(string $query = null): ?array
    {
        return $this->rows($query)[0] ?? null;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    function field(string $query = null): mixed
    {
        $row = $this->row($query);
        return $row[array_key_first($row)] ?? null;
    }

    /**
     * @inheritDoc
     */
    function prepare(string $query): static
    {
        $this->stmt = $this->connect()->prepare($query);
        $this->result = null;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    function bind(mixed $value, int $type = null): static
    {
        if ($this->stmt === null)
            throw new LogicException("Cannot bind value to [null]. No prepared statement found.");

        if($type === null){
            if(is_int($value)) $type = static::INTEGER;
            else if(is_float($value)) $type = static::FLOAT;
            else if(is_string($value)) $type = static::STRING;
            else $type = static::BLOB;
        }

        $type = match ($type) {
            static::INTEGER => "i",
            static::FLOAT => "d",
            static::BLOB => "b",
            default => "s",
        };
        $this->stmt->bind_param($type, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    function execute(): bool
    {
        $success = $this->stmt->execute();
        $this->result = $this->stmt->get_result();
        $this->stmt = null;
        return $success;
    }

    /**
     * @inheritDoc
     */
    function lastInsertedId(): int
    {
        return $this->connect()->insert_id;
    }

    /**
     * @inheritDoc
     */
    function escape(string $string): string
    {
        return $this->connect()->real_escape_string($string);
    }

    /**
     * @inheritDoc
     */
    function beginTransaction()
    {
        $this->connect()->begin_transaction();
    }

    /**
     * @inheritDoc
     */
    function rollbackTransaction()
    {
        $this->connect()->rollback();
    }

    /**
     * @inheritDoc
     */
    function table(string $table): SQLQueryBuilder
    {
        return new SQLQueryBuilder($this, $table);
    }
}