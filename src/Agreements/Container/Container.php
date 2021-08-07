<?php

namespace Curfle\Agreements\Container;

use Closure;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Support\Exceptions\Misc\ClassNotFoundException;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Utilities\Utilities;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

interface Container
{

    /**
     * Binds a singleton instance by its classname or creator-callback to the container.
     *
     * @param string $id
     * @param Closure|string|null $resolver
     */
    public function singleton(string $id, Closure|string|null $resolver);

    /**
     * Binds a binding class by its classname or creator-callback to the container.
     *
     * @param string $id
     * @param Closure|string|null $resolver
     * @param bool $shared
     */
    public function bind(string $id, Closure|string $resolver = null, bool $shared = false);

    /**
     * Creates an instance of a bound class.
     *
     * @param string $id
     * @return object|string
     * @throws BindingResolutionException|ReflectionException
     * @throws CircularDependencyException
     */
    public function make(string $id): object|string;


    /**
     * Binds a concrete instance and returns it.
     *
     * @param string $id
     * @param object $instance
     * @return object|string
     */
    public function instance(string $id, object $instance): object|string;

    /**
     * Alias a type to a different name.
     *
     * @param string
     * @param string $alias
     * @return void
     *
     * @throws LogicException
     */
    public function alias(string $id, string $alias);
}