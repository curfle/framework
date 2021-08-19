<?php

namespace Curfle\Database\Schema;

class Blueprint
{

    /**
     * Table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Holds all generated columns / column changes.
     *
     * @var ForeignKeyConstraint[]
     */
    private array $foreignKeys = [];

    /**
     * Holds all generated foreign keys / foreign key changes.
     *
     * @var BuilderColumn[]
     */
    private array $columns = [];

    /**
     * Holds all foreign keys to drop.
     *
     * @var string[]
     */
    private array $dropForeignKeys = [];

    /**
     * Holds all columns to drop.
     *
     * @var string[]
     */
    private array $dropColumns = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @return BuilderColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return ForeignKeyConstraint[]
     */
    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    /**
     * @return string[]
     */
    public function getDropForeignKeys(): array
    {
        return $this->dropForeignKeys;
    }

    /**
     * @return string[]
     */
    public function getDropColumns(): array
    {
        return $this->dropColumns;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Creates a new id column (alias for int autoincrement).
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function id(string $name): BuilderColumn
    {
        return $this->columns[] = (new BuilderColumn($name, BuilderColumn::TYPE_INT))
            ->length(11)
            ->unsigned()
            ->primary()
            ->autoincrement();
    }

    /**
     * Creates a new string column.
     *
     * @param string $name
     * @param int|null $length
     * @param bool $fixedLength
     * @return BuilderColumn
     */
    public function string(string $name, int $length = null, bool $fixedLength = false): BuilderColumn
    {
        if ($fixedLength === false) {
            if ($length === null)
                return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_TEXT);
            else
                return $this->columns[] = (new BuilderColumn($name, BuilderColumn::TYPE_VARCHAR))->length($length);
        }
        return $this->columns[] = (new BuilderColumn($name, BuilderColumn::TYPE_CHAR))->length($length);
    }

    /**
     * Creates a new boolean (alias for tinyint) column.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function bool(string $name): BuilderColumn
    {
        return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_TINYINT);
    }

    /**
     * Creates a new tinyint column.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function tinyInt(string $name): BuilderColumn
    {
        return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_TINYINT);
    }

    /**
     * Creates a new int column.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function int(string $name): BuilderColumn
    {
        return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_INT);
    }

    /**
     * Creates a new tinyint column.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function bigInt(string $name): BuilderColumn
    {
        return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_BIGINT);
    }

    /**
     * Creates a new float column.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function float(string $name): BuilderColumn
    {
        return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_FLOAT);
    }

    /**
     * Creates a new timestamp column.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function timestamp(string $name): BuilderColumn
    {
        return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_TIMESTAMP);
    }

    /**
     * Creates a new date column.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function date(string $name): BuilderColumn
    {
        return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_DATE);
    }

    /**
     * Creates a new datetime column.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function datetime(string $name): BuilderColumn
    {
        return $this->columns[] = new BuilderColumn($name, BuilderColumn::TYPE_DATETIME);
    }

    /**
     * Creates a deleted column.
     *
     * @return BuilderColumn
     */
    public function softDeletes(): BuilderColumn
    {
        return $this->columns[] = (new BuilderColumn("deleted", BuilderColumn::TYPE_TIMESTAMP))->nullable();
    }

    /**
     * Creates a foreign key constraint.
     *
     * @param string $column
     * @param string|null $name
     * @return ForeignKeyConstraint
     */
    public function foreign(string $column, string $name = null): ForeignKeyConstraint
    {
        return $this->foreignKeys[] = (new ForeignKeyConstraint($name, $this->table))->column($column);
    }

    /**
     * Drops a foreign key constraint.
     *
     * @param string $name
     * @return void
     */
    public function dropForeign(string $name)
    {
        $this->dropForeignKeys[] = $name;
    }

    /**
     * Drops a column.
     *
     * @param string $name
     * @return void
     */
    public function dropColumn(string $name)
    {
        $this->dropColumns[] = $name;
    }
}