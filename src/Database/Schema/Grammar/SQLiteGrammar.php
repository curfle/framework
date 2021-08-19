<?php

namespace Curfle\Database\Schema\Grammar;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Schema\Blueprint;
use Curfle\Database\Schema\BuilderColumn;
use Curfle\Database\Schema\ForeignKeyConstraint;
use Curfle\Support\Exceptions\Database\NoSuchStatement;

class SQLiteGrammar extends SQLGrammar
{

    /**
     * Mappings to MySQL types.
     *
     * @var array|string[]
     */
    private array $typeMapping = [
        BuilderColumn::TYPE_TINYINT => "TINYINT",
        BuilderColumn::TYPE_INT => "INTEGER",
        BuilderColumn::TYPE_BIGINT => "BIGINT",
        BuilderColumn::TYPE_FLOAT => "FLOAT",
        BuilderColumn::TYPE_TEXT => "TEXT",
        BuilderColumn::TYPE_CHAR => "CHAR",
        BuilderColumn::TYPE_VARCHAR => "VARCHAR",
        BuilderColumn::TYPE_DATE => "DATE",
        BuilderColumn::TYPE_DATETIME => "DATETIME",
        BuilderColumn::TYPE_TIMESTAMP => "TIMESTAMP",
    ];

    /**
     * @param SQLConnectorInterface $connector
     * @inheritDoc
     * @throws NoSuchStatement
     */
    public function compileCreateBlueprint(string $name, Blueprint $blueprint, SQLConnectorInterface $connector): string
    {
        // bootstrap blueprint
        $this->boostrapBlueprint($blueprint);

        // build sql
        $sql = "CREATE TABLE `$name` (";

        // columns
        foreach ($blueprint->getColumns() as $column) {
            $sql .= $this->buildColumnDefinition($column, $connector);
            $sql .= ", ";
        }

        // foreign keys
        foreach ($blueprint->getForeignKeys() as $foreignKey) {
            $sql .= $this->buildForeignKeyDefinition($foreignKey, $connector);
            $sql .= ", ";
        }

        $sql = substr($sql, 0, -2);
        $sql .= ")";

        return $sql;
    }

    /**
     * @inheritDoc
     * @throws NoSuchStatement
     */
    public function compileAlterBlueprint(string $name, Blueprint $blueprint, SQLConnectorInterface $connector): string
    {
        // bootstrap blueprint
        $this->boostrapBlueprint($blueprint);

        // build sql
        $sql = "ALTER TABLE `$name` ";

        // change columns
        foreach ($blueprint->getColumns() as $column) {
            if (!$column->isChanged()) {
                $sql .= "ADD COLUMN " . $this->buildColumnDefinition($column, $connector);
            } else {
                throw new NoSuchStatement("SQLite does not support changing columns");
            }
            $sql .= ", ";
        }

        // add foreign keys
        if (!empty($blueprint->getForeignKeys()))
            throw new NoSuchStatement("SQLite does not support adding foreign keys to existing tables");

        // drop foreign keys
        if (!empty($blueprint->getDropForeignKeys()))
            throw new NoSuchStatement("SQLite does not support dropping foreign keys from existing tables");

        // drop columns
        if (!empty($blueprint->getDropColumns()))
            throw new NoSuchStatement("SQLite does not support dropping columns from existing tables");


        $sql = substr($sql, 0, -2);

        return $sql;
    }

    /**
     * Builds a MySQL column definition based on a BuilderColumn instance.
     *
     * @param BuilderColumn $column
     * @param SQLConnectorInterface $connector
     * @return string
     * @throws NoSuchStatement
     */
    private function buildColumnDefinition(BuilderColumn $column, SQLConnectorInterface $connector): string
    {
        if($column->shouldUseCurrentOnUpdate())
            throw new NoSuchStatement("SQLite does not support updating timestamps ON UPDATE");

        // see https://www.sqlite.org/lang_createtable.html
        return $column->getName() . " "
            . $this->typeMapping[$column->getType()]
            . (($column->getLength() !== null && !$column->isPrimary()) ? "({$column->getLength()}) " : " ")
            . (!$column->isNullable() ? "NOT NULL " : "")
            . ($column->hasDefault() ? "DEFAULT " . (
                $column->shouldUseCurrent()
                    ? "CURRENT_TIMESTAMP "
                    : ($column->getDefault() !== null
                        ? "'{$connector->escape($column->getDefault())}'"
                        : "NULL") . " "
                ) : "")
            . ($column->isUnique() ? "UNIQUE " : "")
            . ($column->isPrimary() ? "PRIMARY KEY " . ($column->isAutoincrement() ? "AUTOINCREMENT " : "") : "");
    }

    /**
     * Builds a MySQL foreign key definition based on a ForeignKeyConstraint instance.
     *
     * @param ForeignKeyConstraint $foreignKey
     * @param SQLConnectorInterface $connector
     * @return string
     */
    private function buildForeignKeyDefinition(ForeignKeyConstraint $foreignKey, SQLConnectorInterface $connector): string
    {
        // see https://www.sqlite.org/lang_createtable.html
        return "CONSTRAINT {$foreignKey->getName()} FOREIGN KEY ({$foreignKey->getColumn()}) "
            . "REFERENCES `" . $foreignKey->getOn() . "`({$foreignKey->getReferences()}) "
            . ($foreignKey->getOnDelete() !== null ? "ON DELETE " . $foreignKey->getOnDelete() . " " : "")
            . ($foreignKey->getOnUpdate() !== null ? "ON UPDATE " . $foreignKey->getOnUpdate() . " " : "");
    }
}