<?php

namespace Curfle\Agreements\Database\Schema;

use Closure;

interface ForeignKeyConstraint
{

    /**
     * Sets the column that is referenced.
     *
     * @param string $column
     * @return ForeignKeyConstraint
     */
    public function column(string $column): static;

    /**
     * Sets the target column that is referenced.
     *
     * @param string $column
     * @return ForeignKeyConstraint
     */
    public function references(string $column): static;

    /**
     * Sets the table that is referenced
     *
     * @param string $table
     * @return ForeignKeyConstraint
     */
    public function on(string $table): static;

    /**
     * Sets the on delete action.
     *
     * @param string $action
     * @return ForeignKeyConstraint
     */
    public function onDelete(string $action): static;

    /**
     * Sets the on update action.
     *
     * @param string $action
     * @return ForeignKeyConstraint
     */
    public function onUpdate(string $action): static;

    /**
     * Sets the name of the constraint.
     *
     * @param string $name
     * @return ForeignKeyConstraint
     */
    public function name(string $name): static;

}