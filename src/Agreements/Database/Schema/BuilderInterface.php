<?php

namespace Curfle\Agreements\Database\Schema;

use Closure;

interface BuilderInterface
{
    /**
     * Creates a new table.
     *
     * @param string $table
     * @param Closure $callback
     * @return $this
     */
    public function create(string $table, Closure $callback): static;

    /**
     * Drops an existsing table.
     *
     * @param string $table
     * @return $this
     */
    public function drop(string $table): static;

    /**
     * Drops a table if it exists.
     *
     * @param string $table
     * @return $this
     */
    public function dropIfExists(string $table): static;

    /**
     * Renames an existsing table.
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function rename(string $from, string $to): static;

    /**
     * Alters an existsing table.
     *
     * @param string $table
     * @param Closure $callback
     * @return $this
     */
    public function table(string $table, \Closure $callback): static;

    /**
     * Returns if an existsing table has a column.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function hasColumn(string $table, string $column): bool;

    /**
     * Drops a column in an existsing table.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function dropColumn(string $table, string $column): bool;

    /**
     * Returns if a table exists in the current schema.
     *
     * @param string $table
     * @return bool
     */
    public function hasTable(string $table): bool;
}