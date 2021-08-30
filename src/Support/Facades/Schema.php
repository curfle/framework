<?php

namespace Curfle\Support\Facades;

/**
 * @method static \Curfle\Database\Schema\Builder create(string $table, \Closure $callback)
 * @method static \Curfle\Database\Schema\Builder drop(string $table)
 * @method static \Curfle\Database\Schema\Builder dropIfExists(string $table)
 * @method static \Curfle\Database\Schema\Builder rename(string $from, string $to)
 * @method static \Curfle\Database\Schema\Builder table(string $table, \Closure $callback)
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool dropColumn(string $table, string $column)
 * @method static bool hasTable(string $table)
 *
 * @see \Curfle\Database\Schema\Builder
 */
class Schema extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return mixed
     */
    protected static function getFacadeAccessor() : mixed
    {
        return static::$app['db']->connector()->getSchemaBuilder();
    }
}