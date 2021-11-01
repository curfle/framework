<?php

namespace Curfle\DAO;

use Curfle\Agreements\DAO\DAOInterface;
use Curfle\DAO\Relationships\ManyToManyRelationship;
use Curfle\DAO\Relationships\ManyToOneRelationship;
use Curfle\DAO\Relationships\OneToManyRelationship;
use Curfle\DAO\Relationships\OneToOneRelationship;
use Curfle\DAO\Relationships\Relationship;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Query\SQLQueryBuilder;
use Curfle\Support\Exceptions\DAO\UndefinedPropertyException;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

/**
 * @method static SQLQueryBuilder distinct()
 * @method static SQLQueryBuilder value()
 * @method static SQLQueryBuilder valueAs(string $value, string $as)
 * @method static SQLQueryBuilder join(string $table, string $columnA, string $operator, string $columnB)
 * @method static SQLQueryBuilder leftJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static SQLQueryBuilder leftOuterJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static SQLQueryBuilder rightJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static SQLQueryBuilder rightOuterJoin(string $table, string $columnA, string $operator, string $columnB)
 * @method static SQLQueryBuilder crossJoin(string $table)
 * @method static SQLQueryBuilder where()
 * @method static SQLQueryBuilder orWhere()
 * @method static SQLQueryBuilder whereBetween(string $column, $min, $max)
 * @method static SQLQueryBuilder orWhereBetween(string $column, $min, $max)
 * @method static SQLQueryBuilder whereNotBetween(string $column, $min, $max)
 * @method static SQLQueryBuilder orWhereNotBetween(string $column, $min, $max)
 * @method static SQLQueryBuilder having()
 * @method static SQLQueryBuilder groupBy()
 * @method static SQLQueryBuilder orderBy()
 * @method static SQLQueryBuilder limit(int $n)
 * @method static SQLQueryBuilder offset(int $n)
 * @method static SQLQueryBuilder insert(array $data)
 * @method static SQLQueryBuilder insertOrIgnore(array $data)
 * @method static SQLQueryBuilder insertOrUpdate(array $data)
 * @method static string build()
 * @method static array|null first()
 * @method static array|null find($id)
 * @method static mixed max()
 * @method static mixed min()
 * @method static mixed avg()
 * @method static mixed count()
 * @method static bool exists()
 * @method static bool doesntExist()
 *
 * @see SQLQueryBuilder
 */
abstract class Model implements DAOInterface
{
    /**
     * The SQL connector class.
     *
     * @var SQLConnectorInterface|null
     */
    static SQLConnectorInterface|null $connector = null;

    /**
     * returns the dao's config.
     * config should look like this example for a user table:
     * [
     *  "table" => "users",
     *  "primaryKey" => "id",
     *  "softDelete" => false,
     *  "fields" => [
     *      "id",           // or "myId" => "id", which means that the variable $this->myId gets the value of the database's id column
     *      "firstname",
     *      "lastname",
     *      "email",
     *      "password"
     *  ],
     * ]
     * @return array
     */
    abstract static function config(): array;

    /**
     * Sets the connector class.
     *
     * @param SQLConnectorInterface $connector
     */
    public static function setConnector(SQLConnectorInterface $connector)
    {
        self::$connector = $connector;
    }

    /**
     * Returns the cleaned version of the SQL config.
     *
     * @return array
     * @throws Exception
     */
    public static function __getCleanedConfig(): array
    {
        $config = call_user_func(get_called_class() . "::config") ?? [];

        // ensure all necesarry config keys exist
        if (!isset($config["table"]))
            throw new Exception("One of the following properties is missing in the DAO's SQL configuration: table");

        // default primaryKey to "id"
        if (!isset($config["primaryKey"]))
            $config["primaryKey"] = "id";

        // default softDelete to false
        if (!isset($config["softDelete"]))
            $config["softDelete"] = false;

        // ensure fields are array
        $fields = $config["fields"] ?? [];
        $ignoreFields = array_merge($config["fields"] ?? [], ["connector"]);
        if (empty($fields)) {
            // autofill keys by properties with public class variables
            $fields = array_filter(array_keys(get_class_vars(get_called_class())), function ($field) use ($ignoreFields) {
                return !in_array($field, $ignoreFields);
            });
        }

        if (!is_array($fields))
            throw new Exception("The SQL config's \"fields\" property must be an array");

        // make array assoc if not already
        if (array_keys($fields) === range(0, count($fields) - 1)) {
            $config["fields"] = [];
            foreach ($fields as $field) {
                $config["fields"][$field] = $field;
            }
        } else {
            $config["fields"] = $fields;
        }

        return $config;
    }

    /**
     * Returns the constructor's arguments.
     *
     * @return array
     */
    private static function __getConstructorArguments(): array
    {
        $constructor = (new ReflectionClass(get_called_class()))->getConstructor();

        if ($constructor === null)
            return [];

        return array_map(function (ReflectionParameter $arg) {
            return $arg->getName();
        }, $constructor->getParameters());
    }

    /**
     * Creates an instance of the object from an array.
     *
     * @param array|null $arr
     * @return static|null
     * @throws ReflectionException
     */
    public static function __createInstanceFromArray(?array $arr): static|null
    {
        if ($arr === null)
            return null;

        $className = get_called_class();
        $arguments = self::__getConstructorArguments();
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");

        // re-map keys from result according to config
        foreach ($config["fields"] as $classVar => $dbVar) {
            if ($classVar === $dbVar) continue;
            if (isset($arr[$dbVar])) {
                $arr[$classVar] = $arr[$dbVar];
                unset($arr[$dbVar]);
            }
        }

        // create instance of class and pass all possible values by constructor
        $instance = empty($arguments) ? new $className() : (new ReflectionClass(get_called_class()))->newInstanceArgs(
            array_map(function ($argument) use ($arr) {
                return $arr[$argument] ?? null;
            }, $arguments));

        // set values that are left here
        foreach ($arguments as $argument)
            unset($arr[$argument]);

        foreach ($arr as $property => $value) {
            $instance->$property = $value;
        }

        return $instance;
    }

    /**
     * Calls the ::table function on the connector.
     *
     * @param string $table
     * @return SQLQueryBuilder|void
     */
    public static function __callTableOnConnector(string $table)
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        $statement = self::$connector->table($table);
        if($config["softDelete"] && $table === $config["table"])
            $statement = $statement->where("deleted", null);
        return $statement;
    }

    /**
     * Returns an array for the database with all available column-value associaions.
     *
     * @param array $fields
     * @return array
     * @throws ReflectionException
     */
    private function getColumnPropertyValueMapping(array $fields): array
    {
        $this_ = $this;
        $flippedFields = array_flip($fields);
        $filteredFields = array_filter($flippedFields, function ($field) use ($this_) {
            return (new ReflectionProperty($this_, $field))->isInitialized($this_);
        });
        return array_map(function ($field) use ($this_) {
            return $this_->$field ?? null;
        }, $filteredFields);
    }

    /**
     * Calls the ::lastInsertedId function on the connector.
     *
     * @return SQLQueryBuilder|void
     */
    private static function __callLastInsertedIdOnConnector()
    {
        if (self::$connector instanceof SQLConnectorInterface)
            return self::$connector->lastInsertedId();
        else
            call_user_func(self::$connector . "::lastInsertedId");
    }

    /**
     * @inheritDoc
     */
    public function primaryKey(): mixed
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        return $this->{$config["primaryKey"]};
    }

    /**
     * @inheritDoc
     */
    public static function all(): array
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        $entries = static::__callTableOnConnector($config["table"])
            ->get();

        return array_map(function ($entry) {
            return self::__createInstanceFromArray($entry);
        }, $entries);
    }

    /**
     * @inheritDoc
     */
    public static function get($id): ?static
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        $entry = static::__callTableOnConnector($config["table"])
            ->where($config["primaryKey"], $id)
            ->first();
        return self::__createInstanceFromArray($entry);
    }

    /**
     * @inheritDoc
     */
    public static function create(array $data): ?static
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        $success = static::__callTableOnConnector($config["table"])
            ->insert($data);

        if (!$success)
            return null;

        $id = static::__callLastInsertedIdOnConnector();
        return self::get($id);
    }

    /**
     * @inheritDoc
     */
    public function update(): bool
    {
        $this_ = $this;
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        $primaryKeyField = array_flip($config["fields"])[$config["primaryKey"]];
        return static::__callTableOnConnector($config["table"])
            ->where($config["primaryKey"], $this->$primaryKeyField)
            ->update($this->getColumnPropertyValueMapping($config["fields"]));
    }

    /**
     * @inheritDoc
     */
    public function store(): bool
    {
        $this_ = $this;
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        $primaryKeyField = array_flip($config["fields"])[$config["primaryKey"]];
        $success = static::__callTableOnConnector($config["table"])
            ->insert($this->getColumnPropertyValueMapping($config["fields"]));

        if ($success)
            $this->$primaryKeyField = static::__callLastInsertedIdOnConnector();

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        $primaryKeyField = array_flip($config["fields"])[$config["primaryKey"]];
        $statement = static::__callTableOnConnector($config["table"])
            ->where($config["primaryKey"], $this->$primaryKeyField);

        if ($config["softDelete"])
            return $statement->update(["deleted" => date("Y-m-d H:i:s")]);
        else
            return $statement->delete();
    }

    /**
     * Returns one instance of the referenced class or null.
     *
     * @param string $class
     * @param string|null $fkColumn
     * @return mixed
     */
    protected function hasOne(string $class, string $fkColumnInClass = null): OneToOneRelationship
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");

        if ($fkColumnInClass === null)
            $fkColumnInClass = $config["table"] . "_id";

        return new OneToOneRelationship($this, $class, $fkColumnInClass);
    }

    /**
     * Returns one instance of the referenced class or null.
     *
     * @param string $class
     * @param string|null $fkColumn
     * @return mixed
     */
    protected function belongsTo(string $class, string $fkColumn = null): ManyToOneRelationship
    {
        if ($fkColumn === null) {
            $targetConfig = call_user_func("$class::__getCleanedConfig");
            $fkColumn = $targetConfig["table"] . "_id";
        }
        return new ManyToOneRelationship($this, $class, $fkColumn);
    }

    /**
     * Returns an array of .
     *
     * @param string $class
     * @param string|null $fkColumnInClass
     * @return mixed
     */
    protected function hasMany(string $class, string $fkColumnInClass = null): OneToManyRelationship
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");

        if ($fkColumnInClass === null)
            $fkColumnInClass = $config["table"] . "_id";

        return new OneToManyRelationship($this, $class, $fkColumnInClass);
    }

    /**
     * Returns one instance of the referenced class or null.
     *
     * @param string $class
     * @param string $pivotTableName
     * @param string|null $fkColumnOfCurrentModelInPivotTable
     * @param string|null $fkColumnOfOtherModelInPivotTable
     * @return array
     */
    protected function belongsToMany(
        string $class,
        string $pivotTableName,
        string $fkColumnOfCurrentModelInPivotTable = null,
        string $fkColumnOfOtherModelInPivotTable = null
    ): ManyToManyRelationship
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        $targetConfig = call_user_func("$class::__getCleanedConfig");

        if ($fkColumnOfCurrentModelInPivotTable === null)
            $fkColumnOfCurrentModelInPivotTable = $config["table"] . "_id";

        if ($fkColumnOfOtherModelInPivotTable === null)
            $fkColumnOfOtherModelInPivotTable = $targetConfig["table"] . "_id";

        return new ManyToManyRelationship(
            $this,
            $class,
            $pivotTableName,
            $fkColumnOfCurrentModelInPivotTable,
            $fkColumnOfOtherModelInPivotTable
        );
    }

    /**
     * Returns a method as a property if exists.
     *
     * @throws UndefinedPropertyException
     */
    public function __get(string $name)
    {
        if (method_exists($this, $name)){
            $value = $this->{$name}();
            if($value instanceof Relationship)
                return $value->get();
            else
                return $value;
        }
        throw new UndefinedPropertyException("Undefined property [" . get_class($this) . "::${$name}]");
    }

    /**
     * Passes the function to the query builder (e.g. ::where(...) becomes ::table(...)->where(...)).
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        return static::__callTableOnConnector($config["table"])->{$name}(...$arguments);
    }

}