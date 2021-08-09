<?php

namespace Curfle\Database\Schema;

use Curfle\Agreements\Database\Schema\ForeignKeyConstraint as ForeignKeyConstraintAgreement;
use Curfle\Support\Exceptions\Logic\LogicException;

class ForeignKeyConstraint implements ForeignKeyConstraintAgreement
{
    const RESTRICT = "RESTRICT";
    const CASCADE = "CASCADE";
    const SET_NULL = "SET NULL";
    const NO_ACTION = "NO ACTION";
    const SET_DEFAULT = "SET DEFAULT";

    /**
     * Name of the key.
     *
     * @var string|null
     */
    private ?string $name;

    /**
     * Name of the table.
     *
     * @var string
     */
    private string $table;

    /**
     * Name of the column.
     *
     * @var string|null
     */
    private ?string $column = null;

    /**
     * Column that is referenced.
     *
     * @var string
     */
    private string $references;

    /**
     * Table that is referenced.
     *
     * @var string
     */
    private string $on;

    /**
     * Action that is performed on delete.
     *
     * @var string|null
     */
    private ?string $onDelete = null;

    /**
     * Action that is performed on update.
     *
     * @var string|null
     */
    private ?string $onUpdate = null;

    /**
     * @param string|null $name
     * @param string $table
     */
    public function __construct(?string $name, string $table)
    {
        $this->name = $name;
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function column(string $column): static
    {
        $this->column = $column;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function references(string $column): static
    {
        $this->references = $column;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function on(string $table): static
    {
        $this->on = $table;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function onDelete(string $action): static
    {
        if(!in_array(strtoupper($action), ["RESTRICT", "CASCADE", "SET NULL", "NO ACTION", "SET DEFAULT",]))
            throw new LogicException("Cannot use [$action] on delete for foreign key");

        $this->onDelete = $action;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function onUpdate(string $action): static
    {
        if(!in_array(strtoupper($action), ["RESTRICT", "CASCADE", "SET NULL", "NO ACTION", "SET DEFAULT",]))
            throw new LogicException("Cannot use [$action] on update for foreign key");

        $this->onUpdate = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if($this->name !== null)
            return $this->name;
        else
            return "FK_{$this->getOn()}_{$this->table}";
    }

    /**
     * @return string|null
     */
    public function getColumn(): ?string
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getReferences(): string
    {
        return $this->references;
    }

    /**
     * @return string
     */
    public function getOn(): string
    {
        return $this->on;
    }

    /**
     * @return string|null
     */
    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }

    /**
     * @return string|null
     */
    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }
}