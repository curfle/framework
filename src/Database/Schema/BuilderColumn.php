<?php

namespace Curfle\Database\Schema;

use Closure;
use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Agreements\Database\Schema\BuilderInterface;
use Curfle\Agreements\Database\Schema\Grammar\Grammar;
use Curfle\Agreements\Database\Schema\BuilderColumn as BuilderColumnAgreement;

class BuilderColumn implements BuilderColumnAgreement
{
    // integer (0-99)
    const TYPE_TINYINT = 0;
    const TYPE_INT = 1;
    const TYPE_BIGINT = 2;

    // reals (100-199)
    const TYPE_FLOAT = 100;

    // string (200-299)
    const TYPE_TEXT = 200;
    const TYPE_CHAR = 201;
    const TYPE_VARCHAR = 202;

    // time (300-399)
    const TYPE_DATE = 300;
    const TYPE_DATETIME = 301;
    const TYPE_TIMESTAMP = 302;

    /**
     * The column's type.
     *
     * @var int
     */
    private int $type;

    /**
     * The column's name.
     *
     * @var string
     */
    private string $name;

    /**
     * Marks the column as unique.
     *
     * @var bool
     */
    private bool $unique = false;

    /**
     * The columns' length.
     *
     * @var int|null
     */
    private ?int $length = null;

    /**
     * Indicates if the column is unsigned or not (integer only)
     *
     * @var bool
     */
    private bool $unsigned = false;

    /**
     * Indicates that the column has a default value.
     *
     * @var bool
     */
    private bool $hasDefault = false;

    /**
     * The columns' default value.
     *
     * @var mixed
     */
    private mixed $default = null;

    /**
     * Determines if the columns' default value will be used without escaping.
     *
     * @var bool
     */
    private bool $defaultRaw = false;

    /**
     * Indicates that the column should use CURRENT_TIMESTAMP as default value.
     *
     * @var bool
     */
    private bool $useCurrent = false;

    /**
     * Indicates that the column should use CURRENT_TIMESTAMP as default value on update.
     *
     * @var bool
     */
    private bool $useCurrentOnUpdate = false;

    /**
     * Marks the column as nullable.
     *
     * @var bool
     */
    private bool $nullable = false;

    /**
     * Marks the column as autoincrement.
     *
     * @var bool
     */
    private bool $autoincrement = false;

    /**
     * Marks the column as primary key.
     *
     * @var bool
     */
    private bool $primary = false;

    /**
     * Holds the name of the predecessor column.
     *
     * @var string|null
     */
    private ?string $after = null;

    /**
     * Indicates that the column exists and should be changed.
     *
     * @var bool
     */
    private bool $changed = false;

    /**
     * @param string $name
     * @param int $type
     */
    public function __construct(string $name, int $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function unique(): static
    {
        $this->unique = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function length(int $length): static
    {

        $this->length = $length;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unsigned(): static
    {
        $this->unsigned = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function default(mixed $value, bool $raw = false): static
    {
        $this->hasDefault = true;

        // cast bool to integer
        if (is_bool($value))
            $value = $value ? 1 : 0;

        $this->default = $value;
        $this->defaultRaw = $raw;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function defaultCurrent(): static
    {
        if($this->type === BuilderColumn::TYPE_TIMESTAMP
            || $this->type === BuilderColumn::TYPE_DATETIME){
            $this->hasDefault = true;
            $this->useCurrent = true;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function defaultCurrentOnUpdate(): static
    {
        if($this->type === BuilderColumn::TYPE_TIMESTAMP
            || $this->type === BuilderColumn::TYPE_DATETIME){
            $this->hasDefault = true;
            $this->useCurrentOnUpdate = true;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function nullable(): static
    {
        $this->nullable = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function autoincrement(): static
    {
        $this->autoincrement = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function primary(): static
    {
        $this->primary = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function after(string $column): static
    {
        $this->after = $column;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function change(): static
    {
        $this->changed = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * @return int|null
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * @return bool
     */
    public function isUnsignable(): bool
    {
        return in_array($this->type, [BuilderColumn::TYPE_TINYINT, BuilderColumn::TYPE_INT, BuilderColumn::TYPE_BIGINT]);
    }

    /**
     * @return bool
     */
    public function hasDefault(): bool
    {
        return $this->hasDefault;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @return mixed
     */
    public function useDefaultRaw(): bool
    {
        return $this->defaultRaw;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return bool
     */
    public function isAutoincrement(): bool
    {
        return $this->autoincrement;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * @return string|null
     */
    public function getAfter(): ?string
    {
        return $this->after;
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->changed;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function shouldUseCurrent(): bool
    {
        return $this->useCurrent;
    }

    /**
     * @return bool
     */
    public function shouldUseCurrentOnUpdate(): bool
    {
        return $this->useCurrentOnUpdate;
    }
}