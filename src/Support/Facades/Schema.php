<?php

namespace Curfle\Support\Facades;

use Closure;
use Curfle\Database\Schema\Builder;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use ReflectionException;

/**
 * @method static Builder create(string $table, Closure $callback)
 * @method static Builder drop(string $table)
 * @method static Builder dropIfExists(string $table)
 * @method static Builder rename(string $from, string $to)
 * @method static Builder table(string $table, Closure $callback)
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
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws ReflectionException
     */
    protected static function getFacadeAccessor() : mixed
    {
        return static::$app->resolve('db')->connector()->getSchemaBuilder();
    }
}