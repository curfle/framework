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
     * @var string|null
     */
    static string|null $connector = null;

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
    abstract static function SQLConfig(): array;

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
    private static function __getCleanedSQLConfig(): array
    {
        $config = call_user_func(get_called_class() . "::SQLConfig") ?? [];

        // ensure all necesarry config keys exist
        if (!isset($config["table"]))
            throw new Exception("One of the following properties is missing in the DAO's SQL configurazion: table");

        // default primaryKey to "id"
        if (!isset($config["primaryKey"]))
            $config["primaryKey"] = "id";

        // ensure fields are array
        $fields = $config["fields"] ?? [];
        if (empty($fields)) {
            // autofill keys by properties with public class variables
            $fields = array_filter(array_keys(get_class_vars(get_called_class())), function ($field) {
                return strlen($field) >= 2 && $field[0] != "_" && $field[1] != "_";
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
        $config = call_user_func(get_called_class() . "::__getCleanedSQLConfig");

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
     * @inheritdoc
     */
    public static function all(): array
    {
        $config = call_user_func(get_called_class() . "::__getCleanedSQLConfig");
        $entries = call_user_func(self::$connector . "::table", $config["table"])
            ->get();

        return array_map(function ($entry) {
            return self::__createInstanceFromArray($entry);
        }, $entries);
    }

    /**
     * @inheritdoc
     */
    public static function get($id): ?static
    {
        $config = call_user_func(get_called_class() . "::__getCleanedSQLConfig");
        $entry = call_user_func(self::$connector . "::table", $config["table"])
            ->where($config["primaryKey"], $id)
            ->first();
        return self::__createInstanceFromArray($entry);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public static function create(array $data): ?static
    {
        $config = call_user_func(get_called_class() . "::__getCleanedSQLConfig");
        $success = call_user_func(self::$connector . "::table", $config["table"])
            ->insert($data);

        if (!$success)
            return null;

        $id = call_user_func(self::$connector . "::lastInsertedId");
        return self::get($id);
    }

    /**
     * Entry point for building a query.
     *
     * @return SQLQueryBuilder
     */
    public static function sql(): SQLQueryBuilder
    {
        $config = call_user_func(get_called_class() . "::__getCleanedSQLConfig");
        return call_user_func(self::$connector . "::table", $config["table"]);
    }

    /**
     * @inheritdoc
     */
    public function update(): bool
    {
        $_this = $this;
        $config = call_user_func(get_called_class() . "::__getCleanedSQLConfig");
        $primaryKeyField = array_flip($config["fields"])[$config["primaryKey"]];
        return call_user_func(self::$connector . "::table", $config["table"])
            ->where($config["primaryKey"], $this->$primaryKeyField)
            ->update(array_map(function ($field) use ($_this) {
                return $_this->$field;
            }, array_flip($config["fields"])));
    }

    /**
     * @inheritdoc
     */
    public function store(): bool
    {
        $_this = $this;
        $config = call_user_func(get_called_class() . "::__getCleanedSQLConfig");
        $primaryKeyField = array_flip($config["fields"])[$config["primaryKey"]];
        $success = call_user_func(self::$connector . "::table", $config["table"])
            ->insert(array_map(function ($field) use ($_this) {
                return $_this->$field;
            }, array_flip($config["fields"])));

        if ($success)
            $this->$primaryKeyField = call_user_func(self::$connector . "::lastInsertedId");

        return $success;
    }

    /**
     * @inheritdoc
     */
    public function delete(): bool
    {
        $config = call_user_func(get_called_class() . "::__getCleanedSQLConfig");
        $primaryKeyField = array_flip($config["fields"])[$config["primaryKey"]];
        return call_user_func(self::$connector . "::table", $config["table"])
            ->where($config["primaryKey"], $this->$primaryKeyField)
            ->delete();
    }
}