<?php

namespace Curfle\Agreements\Database\Schema;

use Closure;

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
     * @return BuilderColumn
     */
    public function default(mixed $value): static;

    /**
     * Marks the column to use CURRENT_TIMESTAMP as default value.
     *
     * @return BuilderColumn
     */
    public function useCurrent(): static;

    /**
     * Marks the column to use CURRENT_TIMESTAMP as default value on update.
     *
     * @return BuilderColumn
     */
    public function useCurrentOnUpdate(): static;

    /**
     * Marks the column as nullable.
     *
     * @return BuilderColumn
     */
    public function nullable(): static;

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
     * Indicates that the column should be altered.
     *
     * @return BuilderColumn
     */
    public function change(): static;

    /**
     * @return bool
     */
    public function isUnique(): bool;

    /**
     * @return mixed
     */
    public function getDefault(): mixed;

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
    public function isChanged(): bool;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return string
     */
    public function getName(): string;

}