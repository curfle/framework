<?php

namespace Curfle\Database\Schema;

use Closure;
use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Agreements\Database\Schema\BuilderInterface;
use Curfle\Agreements\Database\Schema\Grammar\Grammar;

abstract class Builder implements BuilderInterface
{

    /**
     * The SQL connector.
     *
     * @var SQLConnectorInterface
     */
    protected SQLConnectorInterface $connector;

    /**
     * The SQL connector.
     *
     * @var Grammar
     */
    protected Grammar $grammar;

    public function __construct(SQLConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @inheritDoc
     */
    public function create(string $table, Closure $callback): static
    {
        // create blueprint
        $blueprint = new Blueprint($table);

        // pass blueprint to the closure
        $callback($blueprint);

        // compile blueprint to string
        $sql = $this->grammar->compileCreateBlueprint($table, $blueprint, $this->connector);

        // execute sql
        $this->connector->prepare($sql);
        $this->connector->execute();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function drop(string $table): static
    {
        $this->connector
            ->prepare("DROP TABLE `$table`")
            ->execute();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function dropIfExists(string $table): static
    {
        $this->connector
            ->prepare("DROP TABLE IF EXISTS `$table`")
            ->execute();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rename(string $from, string $to): static
    {
        $this->connector
            ->prepare("RENAME TABLE `$from` TO `$to`")
            ->execute();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function table(string $table, Closure $callback): static
    {
        // create blueprint
        $blueprint = new Blueprint($table);

        // pass blueprint to the closure
        $callback($blueprint);

        // compile blueprint to string
        $sql = $this->grammar->compileAlterBlueprint($table, $blueprint, $this->connector);

        // execute sql
        $this->connector->prepare($sql);
        $this->connector->execute();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasColumn(string $table, string $column): bool
    {
        return !empty($this->connector->rows("SHOW COLUMNS FROM `$table` LIKE '$column'"));
    }

    /**
     * @inheritDoc
     */
    public function dropColumn(string $table, string $column): bool
    {
        return $this->connector->exec("ALTER TABLE `$table` DROP $column");
    }

    /**
     * @inheritDoc
     */
    public function hasTable(string $table): bool
    {
        return !empty($this->connector->rows("SHOW TABLES LIKE '$table'"));
    }
}