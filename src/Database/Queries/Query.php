<?php

namespace Curfle\Database\Queries;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Queries\Builders\SQLQueryBuilder;
use Curfle\Database\Queries\Builders\StatementData;
use Curfle\Support\Arr;
use Curfle\Support\Exceptions\Logic\LogicException;

/**
 * @method static table(string $table)
 * @method static distinct()
 * @method static select(string $column, string $alias = null)
 * @method static join(string $table, string $columnA, string $operator, string $columnB)
 * @method static leftJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static leftOuterJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static rightJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static rightOuterJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static innerJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static crossJoin(string $table)
 * @method static where(...$condition)
 * @method static orWhere()
 * @method static having()
 * @method static groupBy(...$columns)
 * @method static orderBy()
 * @method static limit(int $n)
 * @method static offset(int $n)
 */
abstract class Query
{
    /**
     * @var SQLQueryBuilder
     */
    protected SQLQueryBuilder $builder;

    /**
     * @var SQLConnectorInterface
     */
    protected SQLConnectorInterface $connector;

    /**
     * @param SQLConnectorInterface $connector
     */
    public function __construct(SQLConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Takes StatementData and prepares a query.
     *
     * @param StatementData $data
     * @return SQLConnectorInterface
     */
    protected function prepareStatement(StatementData $data): SQLConnectorInterface
    {
        // prepare query
        $preparedQuery = $this->connector->prepare($data->getQuery());

        // bind params
        if(!Arr::empty($data->getParams()))
            $preparedQuery->bind($data->getParams());

        return $preparedQuery;
    }

    /**
     * Builds the query to StatementData.
     *
     * @return StatementData
     */
    public function build(): StatementData
    {
        return $this->builder->build();
    }

    /**
     * Returns all rows.
     *
     * @return array|null
     */
    public function get(): array|null
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->build()
        );
        return $connector->rows();
    }

    /**
     * Inserts data.
     *
     * @param array $data
     * @return bool
     * @throws LogicException
     */
    public function insert(array $data): bool
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->insert($data)
                ->build()
        );
        return $connector->execute();
    }

    /**
     * Inserts data.
     *
     * @param array $data
     * @return bool
     * @throws LogicException
     */
    public function insertOrUpdate(array $data): bool
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->insertOrUpdate($data)
                ->build()
        );
        return $connector->execute();
    }

    /**
     * Inserts data.
     *
     * @param array $data
     * @return bool
     * @throws LogicException
     */
    public function insertOrIgnore(array $data): bool
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->insertOrIgnore($data)
                ->build()
        );
        return $connector->execute();
    }

    /**
     * Inserts data.
     *
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->update($data)
                ->build()
        );
        return $connector->execute();
    }

    /**
     * Deletes data.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->delete()
                ->build()
        );
        return $connector->execute();
    }

    /**
     * Returns the first row.
     *
     * @return array|null
     */
    public function first(): array|null
    {

        $connector = $this->prepareStatement(
            $this->builder
                ->limit(1)
                ->build()
        );

        return $connector->row();
    }

    /**
     * Returns the column's value of the first matching entry.
     *
     * @param string $column
     * @return mixed
     */
    public function value(string $column): mixed
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->select($column)
                ->limit(1)
                ->build()
        );
        return $connector->field();
    }

    /**
     * Returns the first row with the specified given identifier.
     *
     * @param mixed $id
     * @param string $column
     * @return array|null
     */
    public function find(mixed $id, string $column = "id"): array|null
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->where($column, $id)
                ->limit(1)
                ->build()
        );
        return $connector->row();
    }

    /**
     * Returns the maximum value of this column.
     *
     * @param string $aggregate
     * @param string $column
     * @return mixed
     */
    public function aggregate(string $aggregate, string $column): mixed
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->select("$aggregate($column)")
                ->build()
        );
        return $connector->field();
    }

    /**
     * Returns the number of entries.
     *
     * @param string $column
     * @return mixed
     */
    public function count(string $column = "*"): mixed
    {
        return $this->aggregate("count", $column);
    }

    /**
     * Returns the maximum value of this column.
     *
     * @param string $column
     * @return mixed
     */
    public function max(string $column): mixed
    {
        return $this->aggregate("max", $column);
    }

    /**
     * Returns the minimum value of this column.
     *
     * @param string $column
     * @return mixed
     */
    public function min(string $column): mixed
    {
        return $this->aggregate("min", $column);
    }

    /**
     * Returns the average value of this column.
     *
     * @param string $column
     * @return mixed
     */
    public function avg(string $column): mixed
    {
        return $this->aggregate("avg", $column);
    }

    /**
     * Returns the summed value of this column.
     *
     * @param string $column
     * @return mixed
     */
    public function sum(string $column): mixed
    {
        return $this->aggregate("sum", $column);
    }

    /**
     * Returns wether the queried row exists or not.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $connector = $this->prepareStatement(
            $this->builder
                ->clearSelect()
                ->select("COUNT(1)")
                ->build()
        );
        return intval($connector->field()) === 1;
    }

    /**
     * Returns wether the queried row does not exist or not.
     *
     * @return bool
     */
    public function doesntExist(): bool
    {
        return !$this->exists();
    }

    /**
     * Magic __call__ method for passing calls to the QueryBuilder.
     *
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public function __call(string $name, array $arguments): static
    {
        $this->builder->{$name}(...$arguments);
        return $this;
    }
}