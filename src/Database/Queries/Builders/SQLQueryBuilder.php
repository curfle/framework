<?php

namespace Curfle\Database\Queries\Builders;

use Curfle\Support\Arr;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Support\Str;

abstract class SQLQueryBuilder
{
    public const SELECT = "SELECT";
    public const UPDATE = "UPDATE";
    public const INSERT = "INSERT";
    public const DELETE = "DELETE";

    protected const DISTINCT = "DISTINCT";
    protected const FROM = "FROM";
    protected const ON = "ON";
    protected const WHERE = "WHERE";
    protected const HAVING = "HAVING";
    protected const GROUP_BY = "GROUP BY";
    protected const ORDER_BY = "ORDER BY";
    protected const LIMIT = "LIMIT";
    protected const OFFSET = "OFFSET";
    protected const OR_REPLACE = "OR REPLACE";
    protected const IGNORE = "IGNORE";
    protected const OR_IRGNORE = "OR IRGNORE";
    protected const INTO = "INTO";
    protected const VALUES = "VALUES";
    protected const ON_DUPLICATE_KEY_UPDATE = "ON DUPLICATE KEY UPDATE";
    protected const SET = "SET";

    /**
     * @var string
     */
    protected string $table;

    /**
     * @var string
     */
    protected string $operation = self::SELECT;

    /**
     * @var array|null
     */
    protected array|null $select = null;

    /**
     * @var bool
     */
    protected bool $distinct = false;

    /**
     * @var array
     */
    protected array $where = [];

    /**
     * @var array
     */
    protected array $groupBy = [];

    /**
     * @var array
     */
    protected array $having = [];

    /**
     * @var array
     */
    protected array $orderBy = [];

    /**
     * @var array
     */
    protected array $join = [];

    /**
     * @var int|null
     */
    protected int|null $offset = null;

    /**
     * @var int|null
     */
    protected int|null $limit = null;

    /**
     * @var array|null
     */
    protected array|null $insertData = null;

    /**
     * @var array|null
     */
    protected array|null $updateData = null;

    /**
     * @var bool
     */
    protected bool $updateOnDuplicateKey = false;

    /**
     * @var bool
     */
    protected bool $ignoreOnExists = false;

    /**
     * @var array
     */
    protected array $boundParams = [];

    /**
     * Sets the table to operate on.
     *
     * @param string $table
     * @return $this
     */
    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Marks the query as distinct.
     *
     * @return $this
     */
    public function distinct(): SQLQueryBuilder
    {
        $this->distinct = !$this->distinct;
        return $this;
    }

    /**
     * Selects a column.
     *
     * @param string $column
     * @param string|null $alias
     * @return $this
     */
    public function select(string $column, string $alias = null): static
    {
        // init select if unused before
        if ($this->select === null) {
            $this->select = [];
        }

        // add column
        $this->select[] = [$column, $alias];
        return $this;
    }

    /**
     * Clears all selects.
     *
     * @return $this
     */
    public function clearSelect(): static
    {
        $this->select = [];
        return $this;
    }

    /**
     * Adds an inner join to the query.
     *
     * @param string $table
     * @param string $columnA
     * @param string $operator
     * @param string $columnB
     * @return $this
     */
    public function join(string $table, string $columnA, string $operator, string $columnB): SQLQueryBuilder
    {
        $this->addJoin("JOIN", $table, $columnA, $operator, $columnB);
        return $this;
    }

    /**
     * Adds a left join to the query.
     *
     * @param string $table
     * @param string $columnA
     * @param string $operator
     * @param string $columnB
     * @return $this
     */
    public function leftJoin(string $table, string $columnA, string $operator, string $columnB): SQLQueryBuilder
    {
        $this->addJoin("LEFT JOIN", $table, $columnA, $operator, $columnB);
        return $this;
    }

    /**
     * Adds a left outer join to the query.
     *
     * @param string $table
     * @param string $columnA
     * @param string $operator
     * @param string $columnB
     * @return $this
     */
    public function leftOuterJoin(string $table, string $columnA, string $operator, string $columnB): SQLQueryBuilder
    {
        $this->addJoin("LEFT OUTER JOIN", $table, $columnA, $operator, $columnB);
        return $this;
    }

    /**
     * Adds a right join to the query.
     *
     * @param string $table
     * @param string $columnA
     * @param string $operator
     * @param string $columnB
     * @return $this
     */
    public function rightJoin(string $table, string $columnA, string $operator, string $columnB): SQLQueryBuilder
    {
        $this->addJoin("RIGHT JOIN", $table, $columnA, $operator, $columnB);
        return $this;
    }

    /**
     * Adds a right outer join to the query.
     *
     * @param string $table
     * @param string $columnA
     * @param string $operator
     * @param string $columnB
     * @return $this
     */
    public function rightOuterJoin(string $table, string $columnA, string $operator, string $columnB): SQLQueryBuilder
    {
        $this->addJoin("RIGHT OUTER JOIN", $table, $columnA, $operator, $columnB);
        return $this;
    }

    /**
     * Adds an inner join to the query.
     *
     * @param string $table
     * @param string $columnA
     * @param string $operator
     * @param string $columnB
     * @return $this
     */
    public function innerJoin(string $table, string $columnA, string $operator, string $columnB): SQLQueryBuilder
    {
        $this->addJoin("INNER JOIN", $table, $columnA, $operator, $columnB);
        return $this;
    }

    /**
     * Adds a cross join to the query.
     *
     * @param string $table
     * @return $this
     */
    public function crossJoin(string $table): SQLQueryBuilder
    {
        $this->addJoin("CROSS JOIN", $table, "", "", "");
        return $this;
    }

    /**
     * Adds a join to the query.
     *
     * @param string $type
     * @param string $table
     * @param string $columnA
     * @param string $operator
     * @param string $columnB
     * @return void
     */
    protected function addJoin(string $type, string $table, string $columnA, string $operator, string $columnB)
    {
        $this->join[] = [
            $type,
            $table,
            $columnA,
            $operator,
            $columnB
        ];
    }

    /**
     * Sets where conditions.
     * Excepted syntaxes are:
     *  ->where('id=5')         =   WHERE id=5
     *  ->where('id', 5)        =   WHERE id=5
     *  ->where('id', '=', 5)   =   WHERE id<=5
     *
     * @return $this
     */
    public function where(): SQLQueryBuilder
    {
        $parsedCondition = call_user_func_array([$this, "parseWhereCondition"], ["AND", func_get_args()]);
        if ($parsedCondition !== null) {
            $this->where[] = $parsedCondition;
        }

        return $this;
    }

    /**
     * Sets where conditions concatenated with OR.
     * Excepted syntaxes are:
     *  ->orWhere('id=5')         =   WHERE id=5
     *  ->orWhere('id', 5)        =   WHERE id=5
     *  ->orWhere('id', '=', 5)   =   WHERE id<=5
     *
     * @return $this
     */
    public function orWhere(): SQLQueryBuilder
    {
        $parsedCondition = call_user_func_array([$this, "parseWhereCondition"], ["OR", func_get_args()]);
        if ($parsedCondition !== null) {
            $this->where[] = $parsedCondition;
        }

        return $this;
    }

    /**
     * Sets having conditions.
     * Excepted syntaxes are:
     *  ->having('id=5')         =   HAVING id=5
     *  ->having('id', 5)        =   HAVING id=5
     *  ->having('id', '=', 5)   =   HAVING id<=5
     *
     * @return $this
     */
    public function having(): SQLQueryBuilder
    {
        $parsedCondition = call_user_func_array([$this, "parseWhereCondition"], ["AND", func_get_args()]);
        if ($parsedCondition !== null) {
            $this->having[] = $parsedCondition;
        }
        return $this;
    }

    /**
     * Parses a where condition parameter.
     *
     * @param string $concatenator
     * @param array $args
     * @return array|null
     */
    protected function parseWhereCondition(string $concatenator, array $args): ?array
    {
        // skip if no args available
        if (empty($args))
            return null;

        // check if simple array or OR-clause
        if (!is_array($args[0])) {
            // simple where condition (e.g. ["id", "=", 5])
            return $this->prepareWhereCondition($concatenator, $args);
        } else {
            // concatenated where
            return array_map(function ($condition, $i) use ($concatenator) {
                return call_user_func_array([$this, "parseWhereCondition"], [$i == 0 ? $concatenator : "OR", $condition]);
            }, $args, array_keys($args));
        }
    }

    /**
     * Prepares a parsed where parameter.
     *
     * @param string $concatenator
     * @param array $condition
     * @return array|null
     */
    protected function prepareWhereCondition(string $concatenator, array $condition): ?array
    {
        // build array containing where condition with shape:
        //  [COLUMN, OPERAND (DEFAULT =), VALUE, CONCATENATOR (AND|OR)]

        if (Arr::length($condition) === 1) {
            return [
                $condition[1],
                "",
                "",
                $concatenator];
        } else if (Arr::length($condition) === 2) {
            return [
                $condition[0],
                $condition[1] === null ? "<=>" : "=",
                $condition[1],
                $concatenator
            ];
        } else if (Arr::length($condition) === 3) {
            return [
                $condition[0],
                $condition[1],
                $condition[2],
                $concatenator
            ];
        }
        return null;
    }

    /**
     * Groups result by column.
     *
     * @return $this
     */
    public function groupBy(): SQLQueryBuilder
    {
        $this->groupBy = array_merge($this->groupBy, func_get_args());
        return $this;
    }

    /**
     * Orders by specific columns.
     * Excepted syntaxes are:
     *  ->orderBy('name')
     *  ->orderBy('name', 'ASC')
     * @return $this
     */
    public function orderBy(): SQLQueryBuilder
    {
        $args = func_get_args();
        $this->orderBy[] = [$args[0], $args[1] ?? ""];
        return $this;
    }

    /**
     * Sets the limit of rows returned.
     *
     * @param int $n
     * @return SQLQueryBuilder
     */
    public function limit(int $n): SQLQueryBuilder
    {
        $this->limit = $n;
        return $this;
    }

    /**
     * Sets the offset in selected rows.
     *
     * @param int $n
     * @return SQLQueryBuilder
     */
    public function offset(int $n): SQLQueryBuilder
    {
        $this->offset = $n;
        return $this;
    }

    /**
     * Inserts rows into the database.
     *
     * @throws LogicException
     */
    public function insert(array $data): static
    {
        $this->operation = self::INSERT;

        // check if one or multiple rows are inserted
        $isSingleRow = !is_array($data[array_keys($data)[0]]);

        if ($isSingleRow) {
            $this->insertData = [$data];
        } else {
            // check if rows share the same keys
            $allRowsShareSameKeys = Arr::length($data[0]) === Arr::length(array_intersect_key(...$data));
            if (!$allRowsShareSameKeys) {
                throw new LogicException("All arrays must share the same key, when inserting multiple entries at once");
            }
            $this->insertData = $data;
        }

        return $this;
    }

    /**
     * Inserts rows into the database or updates them if the key already exists.
     *
     * @throws LogicException
     */
    public function insertOrUpdate(array $data): static
    {
        $this->updateOnDuplicateKey = true;
        return $this->insert($data);
    }

    /**
     * Inserts rows into the database or updates them if the key already exists.
     *
     * @throws LogicException
     */
    public function insertOrIgnore(array $data): static
    {
        $this->ignoreOnExists = true;
        return $this->insert($data);
    }

    /**
     * Updates a row in the database.
     *
     * @param array $data
     * @return static
     */
    public function update(array $data): static
    {
        $this->operation = self::UPDATE;
        $this->updateData = $data;

        return $this;
    }

    /**
     * Deletes a row or multiple rows in the database.
     *
     * @return static
     */
    public function delete(): static
    {
        $this->operation = self::DELETE;
        return $this;
    }

    /**
     * Builds the statement data for execution.
     *
     * @return StatementData
     */
    public function build(): StatementData
    {
        $this->clearBoundParams();

        $query = match ($this->operation) {
            self::SELECT => $this->buildSelectOperation(),
            self::INSERT => $this->buildInsertOperation(),
            self::UPDATE => $this->buildUpdateOperation(),
            self::DELETE => $this->buildDeleteOperation(),
        };

        return new StatementData(Str::trim($query), $this->boundParams);
    }

    /**
     * Builds the SELECT operation.
     *
     * @return string
     */
    protected function buildSelectOperation(): string
    {
        $statement = $this->operation . " ";

        //distinct
        $statement .= $this->distinct
            ? self::DISTINCT . " "
            : "";

        // select
        $statement .= Str::concat(
                array_map(
                    fn($column) => $column[1] === null
                        ? $column[0]
                        : $column[0] . " AS " . $column[1],
                    $this->select ?? [["*", null]]
                ),
                ", ") . " ";

        // table
        $statement .= self::FROM . " $this->table ";

        // joins
        $statement .= !Arr::empty($this->join)
            ? Str::concat(array_map(function ($join) {
                if (!Str::empty($join[2])) {
                    $join[2] = static::ON . " " . $join[2];
                }
                return Str::concat($join);
            }, $this->join)) . " "
            : "";

        // where
        $statement .= $this->buildWhereCondition($this->where, self::WHERE);

        // group by
        $statement .= !Arr::empty($this->groupBy)
            ? self::GROUP_BY . " " . Str::concat($this->groupBy, ", ") . " "
            : "";

        // having
        $statement .= $this->buildWhereCondition($this->having, self::HAVING);

        // order by
        $statement .= !Arr::empty($this->orderBy)
            ? self::ORDER_BY . " " . Str::concat(array_map(function ($condition) {
                return implode(" ", $condition);
            }, $this->orderBy), ", ") . " "
            : "";

        // limit
        $statement .= $this->limit !== null
            ? self::LIMIT . " $this->limit "
            : "";

        // offset
        $statement .= $this->offset !== null
            ? "OFFSET $this->offset "
            : "";

        return $statement;
    }

    /**
     * Builds the INSERT operation.
     *
     * @return string
     */
    abstract protected function buildInsertOperation(): string;

    /**
     * Builds the UPDATE operation.
     *
     * @return string
     */
    protected function buildUpdateOperation(): string
    {
        $statement = $this->operation . " ";

        // ignore
        $statement .= $this->ignoreOnExists ? self::IGNORE . " " : "";

        // table
        $statement .= "$this->table ";

        // set
        $statement .= self::SET . " " . Str::concat(
                array_map(
                    fn($value, $key) => "$key=" . static::getBindParameterName(),
                    $this->updateData,
                    Arr::keys($this->updateData)
                ),
                ", ") . " ";
        foreach ($this->updateData as $value) {
            $this->bindParam($value);
        }

        // where
        $statement .= $this->buildWhereCondition($this->where, self::WHERE);

        return $statement;
    }

    /**
     * Builds the DELETE operation.
     *
     * @return string
     */
    protected function buildDeleteOperation(): string
    {
        $statement = $this->operation . " ";

        // table
        $statement .= self::FROM . " $this->table ";

        // where
        $statement .= $this->buildWhereCondition($this->where, "WHERE");

        // limit
        $statement .= $this->limit !== null ? self::LIMIT . " $this->limit " : "";

        return $statement;
    }

    /**
     * @return static
     */
    protected function clearBoundParams(): static
    {
        $this->boundParams = [];
        return $this;
    }


    /**
     * @param array $conditions
     * @param string $prefix
     * @return string
     */
    protected function buildWhereCondition(array $conditions, string $prefix): string
    {
        if (Arr::empty($conditions))
            return "";

        $stringifiedWhereConditions = [];
        foreach ($conditions as $condition) {
            if (Arr::is($condition[0])) {
                // containing multiple or statements
                $stringified = $this->buildWhereCondition($condition, "");
            } else {
                [$column, $operand, $value, $concatenator] = $condition;
                $stringified = !Arr::empty($stringifiedWhereConditions) ? $concatenator . " " : "";
                $stringified .= "$column$operand" . static::getBindParameterName();
                $this->bindParam($value);
            }
            $stringifiedWhereConditions[] = $stringified;
        }

        return $prefix . " " . Str::concat($stringifiedWhereConditions) . " ";
    }

    /**
     * Binds a value for a builded query.
     *
     * @param mixed $value
     * @return SQLQueryBuilder
     */
    protected function bindParam(mixed $value): static
    {
        $this->boundParams[] = $value;
        return $this;
    }

    /**
     * Returns the name for the next bounded parameter.
     *
     * @return string
     */
    abstract protected function getBindParameterName(): string;
}
