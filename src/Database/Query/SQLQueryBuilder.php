<?php

namespace Curfle\Database\Query;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Support\Str;
use Curfle\Utilities\Constants\IgnoreValue;
use Exception;

class SQLQueryBuilder
{
    private SQLConnectorInterface $connector;
    private string $_table;
    private string $_operation = "SELECT";
    private ?array $_fields = null;
    private bool $_distinct = false;
    private array $_where = [];
    private array $_groupBy = [];
    private array $_having = [];
    private array $_orderBy = [];
    private array $_joins = [];
    private ?int $_offset = null;
    private ?int $_limit = null;
    private ?array $_insert = null;
    private bool $_ignoreOnExists = false;
    private bool $_updateOnDuplicateKey = false;
    private ?array $_update = null;

    /**
     * SQLQueryBuilder constructor.
     * @param SQLConnectorInterface $connector
     * @param string $table
     */
    public function __construct(SQLConnectorInterface $connector, string $table)
    {
        $this->connector = $connector;
        $this->_table = $table;
    }

    /**
     * enables distinct select in query
     * @return $this
     */
    public function distinct(): SQLQueryBuilder
    {
        $this->_distinct = true;
        return $this;
    }

    /**
     * selects only specific columns
     * @return $this
     */
    public function value(): SQLQueryBuilder
    {
        $this->_fields = array_merge($this->_fields ?? [], func_get_args());
        return $this;
    }

    /**
     * selects only specific columns
     * @return $this
     */
    public function valueAs(string $value, string $as): SQLQueryBuilder
    {
        $this->_fields = ["$value AS $as"];
        return $this;
    }

    /**
     * adds an inner join to the query
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
     * adds a left join to the query
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
     * adds a left outer join to the query
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
     * adds a right join to the query
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
     * adds a right outer join to the query
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
     * adds a cross join to the query
     * @param string $table
     * @return $this
     */
    public function crossJoin(string $table): SQLQueryBuilder
    {
        $this->addJoin("CROSS JOIN", $table, "", "", "");
        return $this;
    }

    /**
     * adds an join to the query
     * @param string $type
     * @param string $table
     * @param string $columnA
     * @param string $operator
     * @param string $columnB
     * @return void
     */
    private function addJoin(string $type, string $table, string $columnA, string $operator, string $columnB)
    {
        $this->_joins[] = [
            $type,
            $table,
            $columnA,
            $operator,
            $columnB
        ];
    }

    /**
     * sets where conditions
     * ->where('id=5')         =   WHERE id=5
     * ->where('id', 5)        =   WHERE id=5
     * ->where('id', '=', 5)   =   WHERE id<=5
     * @return $this
     */
    public function where(): SQLQueryBuilder
    {
        $parsedCondition = call_user_func_array([$this, "parseWhereCondition"], ["AND", func_get_args()]);
        if ($parsedCondition !== null) $this->_where[] = $parsedCondition;

        return $this;
    }

    /**
     * sets where conditions concatenated with OR
     * ->orWhere('id=5')         =   WHERE id=5
     * ->orWhere('id', 5)        =   WHERE id=5
     * ->orWhere('id', '=', 5)   =   WHERE id<=5
     * @return $this
     */
    public function orWhere(): SQLQueryBuilder
    {
        $parsedCondition = call_user_func_array([$this, "parseWhereCondition"], ["OR", func_get_args()]);
        if ($parsedCondition !== null) $this->_where[] = $parsedCondition;

        return $this;
    }

    /**
     * sets where between condition
     * ->whereBetween('id', 5, 10)         =   WHERE id BETWEEN $min AND $max
     * @return $this
     */
    public function whereBetween(string $column, $min, $max): SQLQueryBuilder
    {
        $this->_where[] = [
            $column,
            "BETWEEN "
            . $this->connector->escape($min)
            . " AND "
            . $this->connector->escape($max),
            new IgnoreValue(),
            "AND"
        ];

        return $this;
    }

    /**
     * sets where between condition concatenated with OR
     * ->orWhereBetween('id', 5, 10)         =   WHERE id BETWEEN $min AND $max
     * @return $this
     */
    public function orWhereBetween(string $column, $min, $max): SQLQueryBuilder
    {
        $this->_where[] = [
            $column,
            "BETWEEN "
            . $this->connector->escape($min)
            . " AND "
            . $this->connector->escape($max),
            new IgnoreValue(),
            "OR"
        ];

        return $this;
    }

    /**
     * sets where not between condition
     * ->whereNotBetween('id', 5, 10)         =   WHERE id NOT BETWEEN $min AND $max
     * @return $this
     */
    public function whereNotBetween(string $column, $min, $max): SQLQueryBuilder
    {
        $this->_where[] = [
            $column,
            "NOT BETWEEN "
            . $this->connector->escape($min)
            . " AND "
            . $this->connector->escape($max),
            new IgnoreValue(),
            "AND"
        ];

        return $this;
    }

    /**
     * sets where not between condition concatenated with OR
     * ->orWhereNotBetween('id', 5, 10)         =   WHERE id NOT BETWEEN $min AND $max
     * @return $this
     */
    public function orWhereNotBetween(string $column, $min, $max): SQLQueryBuilder
    {
        $this->_where[] = [
            $column,
            "NOT BETWEEN "
            . $this->connector->escape($min)
            . " AND "
            . $this->connector->escape($max),
            new IgnoreValue(),
            "OR"
        ];

        return $this;
    }

    /**
     * sets having conditions
     * ->having('id=5')         =   HAVING id=5
     * ->having('id', 5)        =   HAVING id=5
     * ->having('id', '=', 5)   =   HAVING id<=5
     * @return $this
     */
    public function having(): SQLQueryBuilder
    {
        $parsedCondition = call_user_func_array([$this, "parseWhereCondition"], ["AND", func_get_args()]);
        if ($parsedCondition !== null) $this->_having[] = $parsedCondition;
        return $this;
    }

    /**
     * internal function for parsing a where condition parameter
     * @param string $concatenator
     * @param array $args
     * @return array|null
     */
    private function parseWhereCondition(string $concatenator, array $args): ?array
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
     * internal helper function for parseWhereCondition
     * @param string $concatenator
     * @param array $condition
     * @return array|null
     */
    private function prepareWhereCondition(string $concatenator, array $condition): ?array
    {
        // [COLUMN, OPERAND (DEFAULT =), VALUE, CONCATENATOR (AND|OR)]
        if (count($condition) === 1) {
            return [$condition[1], "", "", $concatenator];
        } else {
            // cast bool to 0 or 1
            if (is_bool($condition[1]))
                $condition[1] = $condition[1] ? 1 : 0;

            if (count($condition) === 2) {
                return [
                    $condition[0],
                    $condition[1] === null ? "IS" : "=",
                    $condition[1] === null ? null : $this->connector->escape($condition[1]),
                    $concatenator
                ];
            } else if (count($condition) === 3) {
                return [
                    $condition[0],
                    $condition[1],
                    $condition[2] === null ? null : $this->connector->escape($condition[2]),
                    $concatenator
                ];
            }
        }
        return null;
    }

    /**
     * groups result by column
     * @return $this
     */
    public function groupBy(): SQLQueryBuilder
    {
        $this->_groupBy = array_merge($this->_groupBy ?? [], func_get_args());
        return $this;
    }

    /**
     * selects only specific columns
     * ->orderBy('name')
     * ->orderBy('name', 'ASC')
     * @return $this
     */
    public function orderBy(): SQLQueryBuilder
    {
        $args = func_get_args();
        if (count($args) === 1) {
            $this->_orderBy[] = [$args[0], ""];
        } else if (count($args) === 2) {
            $this->_orderBy[] = [$args[0], $args[1]];
        }
        return $this;
    }

    /**
     * sets the limit of entries returned
     * @param int $n
     * @return SQLQueryBuilder
     */
    public function limit(int $n): SQLQueryBuilder
    {
        $this->_limit = $n;
        return $this;
    }

    /**
     * sets the offset
     * @param int $n
     * @return SQLQueryBuilder
     */
    public function offset(int $n): SQLQueryBuilder
    {
        $this->_offset = $n;
        return $this;
    }

    /**
     * builds the query string
     * @return string
     */
    public function build(): string
    {
        $this_ = $this;
        $sql = "$this->_operation ";

        if ($this->_operation === "SELECT") {
            //distinct
            $sql .= $this->_distinct ? "DISTINCT " : "";
            // fields
            $sql .= implode(", ", $this->_fields ?? ["*"]) . " ";
            // table
            $sql .= "FROM $this->_table ";
            // joins
            $sql .= !empty($this->_joins) ? implode(" ", array_map(function ($join) {
                    if ($join[2] !== "") $join[2] = "ON " . $join[2];
                    return implode(" ", $join);
                }, $this->_joins)) . " " : "";
            // where
            $sql .= $this->buildWhereCondition($this->_where, "WHERE");
            // group by
            $sql .= !empty($this->_groupBy) ? "GROUP BY " . implode(", ", $this->_groupBy) . " " : "";
            // having
            $sql .= $this->buildWhereCondition($this->_having, "HAVING");
            // order by
            $sql .= !empty($this->_orderBy) ? "ORDER BY " . implode(", ", array_map(function ($condition) {
                    return implode(" ", $condition);
                }, $this->_orderBy)) . " " : "";
            // limit
            $sql .= $this->_limit !== null ? "LIMIT $this->_limit " : "";
            // offset
            $sql .= $this->_offset !== null ? "OFFSET $this->_offset " : "";
        } else if ($this->_operation === "INSERT") {
            // update on duplicate key
            if ($this->connector instanceof SQLiteConnector)
                $sql .= $this->_updateOnDuplicateKey ? "OR REPLACE " : "";
            // ignore on duplicate key
            if ($this->connector instanceof MySQLConnector)
                $sql .= $this->_ignoreOnExists ? "IGNORE " : "";
            if ($this->connector instanceof SQLiteConnector)
                $sql .= $this->_ignoreOnExists ? "OR IGNORE " : "";
            // table
            $sql .= "INTO $this->_table ";
            // column names
            $sql .= "(" . implode(", ", array_keys($this->_insert[0])) . ") ";
            // values
            $sql .= "VALUES ";
            $sql .= implode(", ", array_map(function ($insert) use ($this_) {
                    return "(" . implode(", ", array_map(function ($value) use ($this_) {
                            if (is_bool($value))
                                $value = $value ? 1 : 0;
                            if ($value === null)
                                return "NULL";
                            $value = $this->connector->escape($value);
                            return (!is_numeric($value) and !$value instanceof IgnoreValue) ? "'$value'" : $value;
                        }, $insert)) . ")";
                }, $this->_insert)) . " ";
            // update on duplicate key
            if ($this->connector instanceof MySQLConnector)
                $sql .= $this->_updateOnDuplicateKey ? "ON DUPLICATE KEY UPDATE " . implode(", ", array_map(function ($column) {
                        return " $column = VALUES($column)";
                    }, array_keys($this->_insert[0]))) : "";
        } else if ($this->_operation === "UPDATE") {
            // ignore
            $sql .= $this->_ignoreOnExists ? "IGNORE " : "";
            // table
            $sql .= "$this->_table ";
            // set
            $sql .= "SET " . implode(", ", array_map(function ($value, $key) {
                    return "$key=$value";
                }, $this->_update, array_keys($this->_update))) . " ";
            // where
            $sql .= $this->buildWhereCondition($this->_where, "WHERE");
            // order by
            $sql .= !empty($this->_orderBy) ? "ORDER BY " . implode(", ", array_map(function ($condition) {
                    return implode(" ", $condition);
                }, $this->_orderBy)) . " " : "";

        } else if ($this->_operation === "DELETE") {
            // ignore
            $sql .= $this->_ignoreOnExists ? "IGNORE " : "";
            // table
            $sql .= "FROM $this->_table ";
            // where
            $sql .= $this->buildWhereCondition($this->_where, "WHERE");
            // order by
            $sql .= !empty($this->_orderBy) ? "ORDER BY " . implode(", ", array_map(function ($condition) {
                    return implode(" ", $condition);
                }, $this->_orderBy)) . " " : "";
            // limit
            $sql .= $this->_limit !== null ? "LIMIT $this->_limit " : "";
        }

        return Str::trim($sql);
    }

    /**
     * @param array $arr
     * @param string $prefix
     * @param string $logicalConcatenator
     * @return string
     */
    private function buildWhereCondition(array $arr, string $prefix, string $logicalConcatenator = "AND"): string
    {
        $this_ = $this;
        if (empty($arr))
            return "";

        $counter = 0;
        return "$prefix " . implode(" ", array_map(function ($condition) use ($this_, &$counter, &$logicalConcatenator) {
                    // check if simple where statement or OR-Clause
                    if (!is_array($condition[0])) {
                        // simple where statement
                        // add quotes if is not numeric
                        if (!is_numeric($condition[2]) and $condition[2] !== null and !$condition[2] instanceof IgnoreValue) {
                            $condition[2] = "'" . $condition[2] . "'";
                        }
                        // implode to whole statement
                        $str = "";
                        if ($counter > 0)
                            $str .= $condition[3] . " ";

                        unset($condition[3]);
                        if ($condition[2] === null) $condition[2] = "NULL";
                        if ($condition[2] instanceof IgnoreValue) unset($condition[2]);

                        $counter++;
                        return $str . implode(" ", $condition);
                    } else {
                        $str = "";
                        if ($counter > 0)
                            $str .= $condition[0][3];
                        $counter++;
                        return "$str (" . $this_->buildWhereCondition($condition, "", "OR") . ")";
                    }

                }, $arr) ?? []) . " ";
    }

    /**
     * executes the sql query in the passed SQLConnectorInterface and returns all rows
     * @return array
     */
    public function get(): array
    {
        // calls e.g. MySQL->rows('SELECT * FROM ...')
        return $this->connector->rows($this->build());
    }

    /**
     * executes the sql query in the passed SQLConnectorInterface and returns the first row
     * @return array|null
     */
    public function first(): ?array
    {
        // calls e.g. MySQL->row('SELECT * FROM ...')
        $this->limit(1);
        return $this->connector->row($this->build());
    }

    /**
     * executes the sql query in the passed SQLConnectorInterface and returns a single row
     * @param $id
     * @return array|null
     */
    public function find($id): ?array
    {
        // calls e.g. MySQL->row('SELECT * FROM ...')
        $this->where("id", $id);
        $this->limit(1);
        return $this->connector->row($this->build());
    }

    /**
     * selects the maximum of (a) specific, previously selected, column(s)
     * @return mixed
     */
    public function max(): mixed
    {
        $this->_fields = array_merge($this->_fields ?? [], array_map(function ($item) {
            return "max($item)";
        }, func_get_args()));

        if (count($this->_fields) === 1)
            return $this->connector->field($this->build());
        else
            return $this->connector->row($this->build());
    }

    /**
     * selects the minimum of (a) specific, previously selected, column(s)
     * @return mixed
     */
    public function min(): mixed
    {
        $this->_fields = array_merge($this->_fields ?? [], array_map(function ($item) {
            return "min($item)";
        }, func_get_args()));

        if (count($this->_fields) === 1)
            return $this->connector->field($this->build());
        else
            return $this->connector->row($this->build());
    }

    /**
     * selects the average of (a) specific, previously selected, column(s)
     * @return mixed
     */
    public function avg(): mixed
    {
        $this->_fields = array_merge($this->_fields ?? [], array_map(function ($item) {
            return "avg($item)";
        }, func_get_args()));

        if (count($this->_fields) === 1)
            return $this->connector->field($this->build());
        else
            return $this->connector->row($this->build());
    }

    /**
     * counts all rows if no parameters are passed. If parameters are given, each count macro is
     * executed on the according parameter.
     *
     * @return int|array|null
     */
    public function count(): int|array|null
    {
        $this->_fields = array_merge($this->_fields ?? [], array_map(function ($item) {
            return "count($item)";
        }, empty(func_get_args()) ? ["*"] : func_get_args()));

        if (count($this->_fields) === 1)
            return (int) $this->connector->field($this->build());
        else
            return $this->connector->row($this->build());
    }

    /**
     * executes the sql query in the passed SQLConnectorInterface and returns wether the item exists or not
     * @return bool
     */
    public function exists(): bool
    {
        $this->_fields = ["COUNT(1)"];
        return intval($this->connector->field($this->build())) === 1;
    }

    /**
     * executes the sql query in the passed SQLConnectorInterface and returns wether the item exists or not
     * @return bool
     */
    public function doesntExist(): bool
    {
        return !$this->exists();
    }


    /**
     * inserts entries into the database
     * @throws LogicException
     */
    public function insert(array $data): bool
    {
        if (!empty($data)) {
            $this->_operation = "INSERT";

            if (!is_array($data[array_keys($data)[0]])) {
                // single row
                $this->_insert = [$data];
            } else {
                // multiple rows
                // assert all entries have the same key
                if (count($data[0]) !== count(call_user_func_array("array_intersect_key", $data))) {
                    throw new LogicException("All arrays must share the same key, when inserting multiple entries at once");
                }
                $this->_insert = $data;
            }
        }

        // execute query
        return $this->connector->exec($this->build());
    }

    /**
     * inserts entries into the database and ignores them if they already exist
     * @throws Exception
     */
    public function insertOrIgnore(array $data): bool
    {
        $this->_ignoreOnExists = true;
        return $this->insert($data);
    }

    /**
     * inserts entries into the database and updates them if they already exist
     * @throws Exception
     */
    public function insertOrUpdate(array $data): bool
    {
        $this->_updateOnDuplicateKey = true;
        return $this->insert($data);
    }

    /**
     * updates entries in the database
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        $this->_operation = "UPDATE";
        $this->_update = $data;

        foreach ($this->_update as &$value) {
            if (is_bool($value))
                $value = $value ? 1 : 0;
            if ($value === null)
                $value = "NULL";
            else {
                $value = $this->connector->escape($value);
                if (!is_numeric($value))
                    $value = "'$value'";
            }
        }

        // execute query
        return $this->connector->exec($this->build());
    }

    /**
     * deletes entries in the database
     */
    public function delete(): bool
    {
        $this->_operation = "DELETE";

        // execute query
        return $this->connector->exec($this->build());
    }
}
