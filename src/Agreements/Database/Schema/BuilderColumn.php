<?php

namespace Curfle\Agreements\Database\Schema;

interface BuilderColumn
{

    /**
     * Marks the column as unique.
     *
     * @return BuilderColumn
     */
    public function unique(): static;

    /**
     * Sets the length of the column.
     *
     * @param int $length
     * @return BuilderColumn
     */
    public function length(int $length): static;

    /**
     * Marks the columns as unsiged (integer only).
     *
     * @return BuilderColumn
     */
    public function unsigned(): static;

    /**
     * Defaults the column to a value.
     *
     * @param mixed $value
     * @param bool $raw
     * @return BuilderColumn
     */
    public function default(mixed $value, bool $raw = false): static;

    /**
     * Marks the column to use CURRENT_TIMESTAMP as default value.
     *
     * @return BuilderColumn
     */
    public function defaultCurrent(): static;

    /**
     * Marks the column to use CURRENT_TIMESTAMP as default value on update.
     *
     * @return BuilderColumn
     */
    public function defaultCurrentOnUpdate(): static;

    /**
     * Marks the column as nullable.
     *
     * @return BuilderColumn
     */
    public function nullable(): static;

    /**
     * Creates an index on the column.
     *
     * @param string|null $name
     * @return BuilderColumn
     */
    public function index(?string $name = null): static;

    /**
     * Marks the column as autoincrement.
     *
     * @return BuilderColumn
     */
    public function autoincrement(): static;

    /**
     * Marks the column as primary key.
     *
     * @return BuilderColumn
     */
    public function primary(): static;

    /**
     * Sets the predecessor column.
     *
     * @param string $column
     * @return BuilderColumn
     */
    public function after(string $column): static;

    /**
     * Sets the column as first column in the table when altering.
     *
     * @return BuilderColumn
     */
    public function first(): static;

    /**
     * Renames the column to another name.
     *
     * @param string $name
     * @return BuilderColumn
     */
    public function rename(string $name): static;

    /**
     * Indicates that the column should be altered.
     *
     * @return BuilderColumn
     */
    public function change(): static;

    /**
     * Sets the permitted enum values.
     *
     * @param array $values
     * @return $this
     */
    public function values(array $values): static;

    /**
     * @return bool
     */
    public function isUnique(): bool;

    /**
     * @return int|null
     */
    public function getLength(): int|null;

    /**
     * @return bool
     */
    public function isUnsigned(): bool;

    /**
     * @return bool
     */
    public function isUnsignable(): bool;

    /**
     * @return bool
     */
    public function hasDefault(): bool;

    /**
     * @return mixed
     */
    public function getDefault(): mixed;

    /**
     * @return mixed
     */
    public function useDefaultRaw(): bool;

    /**
     * @return bool
     */
    public function shouldUseCurrent(): bool;

    /**
     * @return bool
     */
    public function shouldUseCurrentOnUpdate(): bool;

    /**
     * @return bool
     */
    public function isNullable(): bool;

    /**
     * @return bool
     */
    public function isAutoincrement(): bool;

    /**
     * @return bool
     */
    public function isPrimary(): bool;

    /**
     * @return string|null
     */
    public function getAfter(): ?string;

    /**
     * @return bool
     */
    public function isFirst(): bool;

    /**
     * @return bool
     */
    public function isRenamed(): bool;

    /**
     * @return bool
     */
    public function isChanged(): bool;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string|null
     */
    public function getNewName(): string|null;

    /**
     * @return array|null
     */
    public function getValues(): ?array;

    /**
     * @return bool
     */
    public function shouldCreateIndex(): bool;

    /**
     * @return string|null
     */
    public function getIndexName(): ?string;
}