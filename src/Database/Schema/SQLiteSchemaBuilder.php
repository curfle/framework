<?php

namespace Curfle\Database\Schema;

use Closure;
use Curfle\Agreements\Database\Schema\BuilderInterface;
use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Database\Schema\Grammar\MySQLGrammar;
use Curfle\Database\Schema\Grammar\SQLiteGrammar;
use Curfle\Support\Exceptions\Database\NoSuchStatementException;

class SQLiteSchemaBuilder extends Builder
{

    /**
     * @param SQLiteConnector $connector
     */
    public function __construct(SQLiteConnector $connector)
    {
        parent::__construct($connector);
        $this->grammar = new SQLiteGrammar();
    }

    /**
     * @inheritDoc
     */
    public function rename(string $from, string $to): static
    {
        $this->connector
            ->prepare("ALTER TABLE $from RENAME TO $to")
            ->execute();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasColumn(string $table, string $column): bool
    {
        $columns = $this->connector
            ->prepare("PRAGMA table_info(`$table`);")
            ->rows();

        foreach ($columns as $c)
            if ($c["name"] === $column)
                return true;

        return false;
    }

    /**
     * @inheritDoc
     */
    public function hasTable(string $table): bool
    {
        return !empty($this->connector
            ->prepare("SELECT name FROM sqlite_master WHERE type = ? AND name = ?")
            ->bind("table")
            ->bind($table)
            ->rows());
    }

    /**
     * @throws NoSuchStatementException
     */
    public function dropColumn(string $table, string $column): bool
    {
        throw new NoSuchStatementException("SQLite does not support dropping columns from existing tables");
    }
}