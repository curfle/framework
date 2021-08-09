<?php

namespace Curfle\DAO;

use Curfle\Agreements\DAO\DAOInterface;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Query\SQLQueryBuilder;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

abstract class Model implements DAOInterface
{
    /**
     * The SQL connector class.
     *
     * @var string|SQLConnectorInterface|null
     */
    static string|SQLConnectorInterface|null $connector = null;

    /**
     * returns the dao's config.
     * config should look like this example for a user table:
     * [
     *  "table" => "users",
     *  "primaryKey" => "id",
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
     * @param string $connector
     */
    public static function setConnector(string $connector)
    {
        self::$connector = $connector;
    }

    /**
     * Returns the cleaned version of the SQL config.
     *
     * @return array
     * @throws Exception
     */
    private static function __getCleanedConfig(): array
    {
        $config = call_user_func(get_called_class() . "::config") ?? [];

        // ensure all necesarry config keys exist
        if (!isset($config["table"]))
            throw new Exception("One of the following properties is missing in the DAO's SQL configurazion: table");

        // default primaryKey to "id"
        if (!isset($config["primaryKey"]))
            $config["primaryKey"] = "id";

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
    private static function __createInstanceFromArray(?array $arr): static|null
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
        $instance = empty($arguments) ? new get_called_class() : (new ReflectionClass(get_called_class()))->newInstanceArgs(
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
    private static function __callTableOnConnector(string $table)
    {
        if (self::$connector instanceof SQLConnectorInterface)
            return self::$connector->table($table);
        else
            call_user_func(self::$connector . "::table", $table);
    }

    /**
     * Returns an array for the database with all available column-value associaions.
     *
     * @param array $fields
     * @return array
     */
    private function getColumnPropertyValueMapping(array $fields): array
    {
        $this_ = $this;
        $flippedFields = array_flip($fields);
        $filteredFields = array_filter($flippedFields, function($field) use($this_){
            return isset($this_->$field);
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
     * Entry point for building a query.
     *
     * @return SQLQueryBuilder
     */
    public static function sql(): SQLQueryBuilder
    {
        $config = call_user_func(get_called_class() . "::__getCleanedConfig");
        return static::__callTableOnConnector($config["table"]);
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
        return static::__callTableOnConnector($config["table"])
            ->where($config["primaryKey"], $this->$primaryKeyField)
            ->delete();
    }
}