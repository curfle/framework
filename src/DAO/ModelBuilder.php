<?php

namespace Curfle\DAO;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Queries\Builders\StatementData;
use Curfle\Database\Queries\Query;

/**
 * @method ModelBuilder table(string $table)
 * @method ModelBuilder distinct()
 * @method ModelBuilder select(string $column, string $alias = null)
 * @method ModelBuilder join(string $table, string $columnA, string $operator, string $columnB)
 * @method ModelBuilder leftJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method ModelBuilder leftOuterJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method ModelBuilder rightJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method ModelBuilder rightOuterJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method ModelBuilder crossJoin(string $table)
 * @method ModelBuilder where(...$condition)
 * @method ModelBuilder orWhere()
 * @method ModelBuilder having()
 * @method ModelBuilder groupBy(...$columns)
 * @method ModelBuilder orderBy()
 * @method ModelBuilder limit(int $n)
 * @method ModelBuilder offset(int $n)
 * @method bool insert(array $data)
 * @method bool insertOrUpdate(array $data)
 * @method bool insertOrIgnore(array $data)
 * @method bool update(array $data)
 * @method mixed value(string $column)
 * @method mixed count()
 * @method mixed max()
 * @method mixed min()
 * @method mixed avg()
 * @method mixed sum()
 * @method bool exists()
 * @method bool doesntExist()
 */
class ModelBuilder
{

    /**
     * @var Query
     */
    protected Query $query;

    /**
     * @var string
     */
    protected string $class;

    /**
     * @param string $class
     * @param Query $query
     */
    public function __construct(string $class, Query $query)
    {
        $this->class = $class;
        $this->query = $query;
    }

    /**
     * Returns a ModelBuilder instance from a Query instance.
     *
     * @param Query $query
     * @param string $class
     * @return static
     */
    public static function fromQuery(Query $query, string $class): static
    {
        return new static($class, $query);
    }

    /**
     * Returns all rows as model entries.
     *
     * @return array
     */
    public function get(): array
    {
        return array_map(
            fn($item) => call_user_func($this->class . "::__createInstanceFromArray", $item),
            $this->query->get()
        );
    }

    /**
     * Returns the first row as model entry.
     */
    public function first()
    {
        return call_user_func($this->class . "::__createInstanceFromArray", $this->query->first());
    }

    /**
     * Returns the first row with the specified given identifier as model entry.
     *
     * @param mixed $id
     * @param string $column
     */
    public function find(mixed $id, string $column = "id")
    {
        return call_user_func($this->class . "::__createInstanceFromArray", $this->query->find($id, $column));
    }

    /**
     * Magic __call__ method for passing calls to the Query.
     *
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public function __call(string $name, array $arguments): mixed
    {
        $result = $this->query->{$name}(...$arguments);
        return $result instanceof Query ? $this : $result;
    }
}