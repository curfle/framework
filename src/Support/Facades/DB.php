<?php

namespace Curfle\Support\Facades;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Queries\Query;

/**
 * @method static Query table(string $table)
 * @method static mixed query(string $query)
 * @method static bool execute(string $query)
 * @method static array rows(string $query = null)
 * @method static ?array row(string $query = null)
 * @method static mixed field(string $query = null)
 * @method static SQLConnectorInterface prepare(string $query)
 * @method static SQLConnectorInterface bind(mixed $values, int|array $types = null)
 * @method static mixed lastInsertedId()
 * @method static string escape(string $string)
 * @method static void beginTransaction()
 * @method static void commitTransaction()
 * @method static void rollbackTransaction()
 * @method static SQLConnectorInterface connector(string $name = null)
 *
 * @see \Curfle\Database\DatabaseManager
 */
class DB extends Facade
{

    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor(): string
    {
        return "db";
    }
}