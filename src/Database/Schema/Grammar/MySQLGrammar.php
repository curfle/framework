<?php

namespace Curfle\Database\Schema\Grammar;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Schema\Blueprint;
use Curfle\Database\Schema\BuilderColumn;
use Curfle\Database\Schema\ForeignKeyConstraint;

class MySQLGrammar extends SQLGrammar
{

    /**
     * Mappings to MySQL types.
     *
     * @var array|string[]
     */
    private array $typeMapping = [
        BuilderColumn::TYPE_TINYINT => "TINYINT",
        BuilderColumn::TYPE_INT => "INT",
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
                $sql .= "CHANGE {$column->getName()} " . $this->buildColumnDefinition($column, $connector);
            }
            $sql .= ", ";
        }

        // add foreign keys
        foreach ($blueprint->getForeignKeys() as $foreignKey) {
            $sql .= "ADD " . $this->buildForeignKeyDefinition($foreignKey, $connector);
            $sql .= ", ";
        }

        // drop foreign keys
        foreach ($blueprint->getDropForeignKeys() as $foreignKey) {
            $sql .= "DROP FOREIGN KEY $foreignKey, ";
            $sql .= "DROP INDEX $foreignKey";
            $sql .= ", ";
        }

        // drop columns
        foreach ($blueprint->getDropColumns() as $column) {
            $sql .= "DROP COLUMN $column, ";
        }

        $sql = substr($sql, 0, -2);

        return $sql;
    }

    /**
     * Builds a MySQL column definition based on a BuilderColumn instance.
     *
     * @param BuilderColumn $column
     * @param SQLConnectorInterface $connector
     * @return string
     */
    private function buildColumnDefinition(BuilderColumn $column, SQLConnectorInterface $connector): string
    {
        // data_type [NOT NULL | NULL] [DEFAULT {literal | (expr)} ]
        //      [VISIBLE | INVISIBLE]
        //      [AUTO_INCREMENT] [UNIQUE [KEY]] [[PRIMARY] KEY]
        //      [COMMENT 'string']
        //      [COLLATE collation_name]
        //      [COLUMN_FORMAT {FIXED | DYNAMIC | DEFAULT}]
        //      [ENGINE_ATTRIBUTE [=] 'string']
        //      [SECONDARY_ENGINE_ATTRIBUTE [=] 'string']
        //      [STORAGE {DISK | MEMORY}]
        //      [reference_definition]
        //      [check_constraint_definition]
        return $column->getName() . " "
            . $this->typeMapping[$column->getType()]
            . ($column->getLength() !== null ? "({$column->getLength()}) " : " ")
            . ($column->isUnsignable() && $column->isUnsigned() ? "UNSIGNED " : "")
            . (!$column->isNullable() ? "NOT NULL " : "")
            . ($column->hasDefault() ? "DEFAULT " . (
                $column->shouldUseCurrent()
                    ? "CURRENT_TIMESTAMP "
                    : ($column->getDefault() !== null
                        ? ($column->useDefaultRaw() ? $column->getDefault() : "'{$connector->escape($column->getDefault())}'")
                        : "NULL") . " "
                ) : "") . ($column->shouldUseCurrentOnUpdate() ? "ON UPDATE CURRENT_TIMESTAMP " : "")
            . ($column->isAutoincrement() ? "AUTO_INCREMENT " : "")
            . ($column->isUnique() ? "UNIQUE " : "")
            . ($column->isPrimary() ? "PRIMARY KEY " : "");
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
        // [CONSTRAINT [symbol]] FOREIGN KEY
        //    [index_name] (index_col_name, ...)
        //    REFERENCES tbl_name (index_col_name,...)
        //    [ON DELETE reference_option]
        //    [ON UPDATE reference_option]
        return "CONSTRAINT {$foreignKey->getName()} FOREIGN KEY {$foreignKey->getName()} ({$foreignKey->getColumn()}) "
            . "REFERENCES `" . $foreignKey->getOn() . "`({$foreignKey->getReferences()}) "
            . ($foreignKey->getOnDelete() !== null ? "ON DELETE " . $foreignKey->getOnDelete() . " " : "")
            . ($foreignKey->getOnUpdate() !== null ? "ON UPDATE " . $foreignKey->getOnUpdate() . " " : "");
    }
}