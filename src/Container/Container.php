<?php

namespace Curfle\Container;

use ArrayAccess;
use Closure;
use Curfle\Agreements\Container\Container as ContainerAgreement;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Utilities\Utilities;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class Container implements ContainerAgreement
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static Container $instance;

    /**
     * An array of the types that have been resolved.
     *
     * @var bool[]
     */
    protected array $resolved = [];

    /**
     * The list of all bindings.
     *
     * @var array
     */
    private array $bindings = [];

    /**
     * The container's method bindings.
     *
     * @var Closure[]
     */
    protected array $methodBindings = [];

    /**
     * The list of all resolved bindings.
     *
     * @var array
     */
    private array $instances = [];

    /**
     * The registered type aliases.
     *
     * @var string[]
     */
    protected array $aliases = [];

    /**
     * The registered aliases keyed by their according ids.
     *
     * @var array[]
     */
    protected array $aliasesById = [];

    /**
     * The extension closures for services.
     *
     * @var array[]
     */
    protected array $extenders = [];

    /**
     * The stack of concretions currently being built.
     *
     * @var array[]
     */
    protected array $buildStack = [];

    /**
     * The parameter override stack.
     *
     * @var array[]
     */
    protected array $with = [];

    /**
     * The contextual binding map.
     *
     * @var array[]
     */
    public array $contextual = [];

    /**
     * Determine if a given type is shared.
     *
     * @param string $id
     * @return bool
     */
    public function isShared(string $id): bool
    {
        return isset($this->instances[$id]) || ($this->bindings[$id]['shared'] ?? false);
    }

    /**
     * Determine if a given string is an alias.
     *
     * @param string $name
     * @return bool
     */
    public function isAlias(string $name): bool
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Binds a singleton instance by its classname or creator-callback to the container.
     *
     * @param string $id
     * @param Closure|string|null $resolver
     */
    public function singleton(string $id, Closure|string|null $resolver)
    {
        $this->bind($id, $resolver, true);
    }

    /**
     * Binds a binding class by its classname or creator-callback to the container.
     *
     * @param string $id
     * @param Closure|string|null $resolver
     * @param bool $shared
     */
    public function bind(string $id, Closure|string $resolver = null, bool $shared = false)
    {
        $resolver = $resolver ?? $id;

        if (is_string($resolver))
            $resolver = $this->getClosure($id, $resolver);

        $this->bindings[$id] = compact("resolver", "shared");
    }

    /**
     * Returns a Closure that builds a class of a given class name.
     *
     * @param string $id
     * @param string $resolver
     * @return Closure
     */
    private function getClosure(string $id, string $resolver): Closure
    {
        return function ($container, $parameters = []) use ($id, $resolver) {
            if ($id == $resolver)
                return $container->build($resolver);

            return $container->make($resolver, $parameters);
        };
    }

    /**
     * Determine if the container has a method binding.
     *
     * @param string $method
     * @return bool
     */
    public function hasMethodBinding(string $method): bool
    {
        return isset($this->methodBindings[$method]);
    }

    /**
     * Get the method binding for the given method.
     *
     * @param string $method
     * @param mixed $instance
     * @return mixed
     */
    public function callMethodBinding(string $method, mixed $instance): mixed
    {
        return call_user_func($this->methodBindings[$method], $instance, $this);
    }

    /**
     * Resolves a bound singleton instance from the container.
     *
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws BindingResolutionException
     * @throws ReflectionException|CircularDependencyException
     */
    public function resolve(string $id, array $parameters = []): mixed
    {
        // resolve potential alias(es)
        $id = $this->getAlias($id);

        // get the concrete resolver
        $resolver = $this->getContextualConcrete($id);

        // determine if the resolver needs a contextual build
        $needsContextualBuild = !empty($parameters) || !$resolver === null;

        // if isset an instance (for singleton) -> return it
        if (isset($this->instances[$id]))
            return $this->instances[$id];

        $this->with[] = $parameters;

        // if resolver is null -> obtain resolver by its id
        if ($resolver === null)
            $resolver = $this->getConcrete($id);

        // We're ready to instantiate an instance of the concrete type registered for
        // the binding. This will instantiate the types, as well as resolve any of
        // its "nested" dependencies recursively until all have got resolved.
        if ($this->isBuildable($resolver, $id))
            $object = $this->build($resolver);
        else
            $object = $this->make($resolver);

        // If we defined any extenders for this type, we'll need to spin through them
        // and apply them to the object being built. This allows for the extension
        // of services, such as changing configuration or decorating the object.
        foreach ($this->getExtenders($id) as $extender) {
            $object = $extender($object, $this);
        }

        // If the requested type is registered as a singleton we'll want to cache off
        // the instances in "memory" so we can return it later without creating an
        // entirely new instance of an object on each subsequent request for it.
        if ($this->isShared($id) && !$needsContextualBuild) {
            $this->instances[$id] = $object;
        }

        // Before returning, we will also set the resolved flag to "true" and pop off
        // the parameter overrides for this build. After those two things are done
        // we will be ready to return the fully constructed class instance.
        $this->resolved[$id] = true;

        // clear used parameters
        array_pop($this->with);

        // return resolved object
        return $object;
    }

    /**
     * Get the concrete type for a given abstract.
     *
     * @param string|callable $id
     * @return mixed
     */
    protected function getConcrete(string|callable $id): mixed
    {
        // If we don't have a registered resolver or concrete for the type, we'll just
        // assume each type is a concrete name and will attempt to resolve it as is
        // since the container should be able to resolve concretes automatically.
        if (isset($this->bindings[$id])) {
            return $this->bindings[$id]["resolver"];
        }

        return $id;
    }

    /**
     * Get the contextual concrete binding for the given abstract.
     *
     * @param string|callable $id
     * @return Closure|string|array|null
     */
    protected function getContextualConcrete(string|callable $id): Closure|string|array|null
    {
        if (!is_null($binding = $this->findInContextualBindings($id))) {
            return $binding;
        }

        // Next we need to see if a contextual binding might be bound under an alias of the
        // given abstract type. So, we will need to check if any aliases exist with this
        // type and then spin through them and check for contextual bindings on these.
        if (empty($this->abstractAliases[$id])) {
            return null;
        }

        foreach ($this->abstractAliases[$id] as $alias) {
            if (!is_null($binding = $this->findInContextualBindings($alias))) {
                return $binding;
            }
        }

        return null;
    }

    /**
     * Find the concrete binding for the given abstract in the contextual binding array.
     *
     * @param string|callable $id
     * @return Closure|string|null
     */
    protected function findInContextualBindings(string|callable $id): Closure|string|null
    {
        return $this->contextual[end($this->buildStack)][$id] ?? null;
    }

    /**
     * Determine if the given concrete is buildable.
     *
     * @param mixed $resolver
     * @param string $id
     * @return bool
     */
    protected function isBuildable(mixed $resolver, string $id): bool
    {
        return $resolver === $id || $resolver instanceof Closure;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param Closure|string $resolver
     * @return mixed
     *
     * @throws BindingResolutionException
     * @throws CircularDependencyException|ReflectionException
     */
    public function build(Closure|string $resolver): mixed
    {
        // If the concrete type is actually a Closure, we will just execute it and
        // hand back the results of the functions, which allows functions to be
        // used as resolvers for more fine-tuned resolution of these objects.
        if ($resolver instanceof Closure) {
            return $resolver($this, $this->getLastParameterOverride());
        }

        try {
            $reflector = new ReflectionClass($resolver);
        } catch (ReflectionException $e) {
            throw new BindingResolutionException("Target class [$resolver] does not exist.", 0, $e);
        }

        // If the type is not instantiable, the developer is attempting to resolve
        // an abstract type such as an Interface or Abstract Class and there is
        // no binding registered for the abstractions, so we need to bail out.
        if (!$reflector->isInstantiable()) {
            $this->notInstantiable($resolver);
            return null;
        }

        // detect circular dependencies
        if (in_array($resolver, $this->buildStack))
            throw new CircularDependencyException("Circular dependency detected while resolving [$resolver].");

        // add resolver to buildStack
        $this->buildStack[] = $resolver;

        $constructor = $reflector->getConstructor();

        // If there are no constructors, that means there are no dependencies then
        // we can just resolve the instances of the objects right away, without
        // resolving any other types or dependencies out of these containers.
        if (is_null($constructor)) {
            array_pop($this->buildStack);

            return new $resolver;
        }

        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        try {
            $instances = $this->resolveDependencies($dependencies);
        } catch (BindingResolutionException $e) {
            array_pop($this->buildStack);

            throw $e;
        }

        // remove last item from build stack
        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all the dependencies from the ReflectionParameters.
     *
     * @param ReflectionParameter[] $dependencies
     * @return array
     *
     * @throws BindingResolutionException|ReflectionException|CircularDependencyException
     */
    protected function resolveDependencies(array $dependencies): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // If the dependency has an override for this particular build we will use
            // that instead as the value. Otherwise, we will continue with this run
            // of resolutions and let reflection attempt to determine the result.
            if ($this->hasParameterOverride($dependency)) {
                $results[] = $this->getParameterOverride($dependency);

                continue;
            }

            // If the class is null, it means the dependency is a string or some other
            // primitive type which we can not resolve since it is not a class, and
            // we will just bomb out with an error since we have no-where to go.
            $result = is_null(Utilities::getParameterClassName($dependency))
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);

            if ($dependency->isVariadic()) {
                $results = array_merge($results, is_array($result) ? $result : [$result]);
            } else {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Determine if the given dependency has a parameter override.
     *
     * @param ReflectionParameter $dependency
     * @return bool
     */
    protected function hasParameterOverride(ReflectionParameter $dependency): bool
    {
        return array_key_exists(
            $dependency->name, $this->getLastParameterOverride()
        );
    }

    /**
     * Get a parameter override for a dependency.
     *
     * @param ReflectionParameter $dependency
     * @return mixed
     */
    protected function getParameterOverride(ReflectionParameter $dependency): mixed
    {
        return $this->getLastParameterOverride()[$dependency->name];
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride(): array
    {
        return count($this->with) ? end($this->with) : [];
    }

    /**
     * Resolve a non-class hinted primitive dependency.
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter): mixed
    {
        if (!is_null($resolver = $this->getContextualConcrete('$' . $parameter->getName()))) {
            return $resolver instanceof Closure ? $resolver($this) : $resolver;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $this->unresolvablePrimitive($parameter);
        return null;
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     *
     * @throws BindingResolutionException|ReflectionException
     * @throws CircularDependencyException
     */
    protected function resolveClass(ReflectionParameter $parameter): mixed
    {
        try {
            return $parameter->isVariadic()
                ? $this->resolveVariadicClass($parameter)
                : $this->make(Utilities::getParameterClassName($parameter));
        }

            // If we can not resolve the class instance, we will check to see if the value
            // is optional, and if it is we will return the optional parameter value as
            // the value of the dependency, similarly to how we do this with scalars.
        catch (BindingResolutionException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                array_pop($this->with);

                return $parameter->getDefaultValue();
            }

            if ($parameter->isVariadic()) {
                array_pop($this->with);

                return [];
            }

            throw $e;
        }
    }

    /**
     * Resolve a class based variadic dependency from the container.
     *
     * @param ReflectionParameter $parameter
     * @return object|array
     * @throws BindingResolutionException
     * @throws ReflectionException|CircularDependencyException
     */
    protected function resolveVariadicClass(ReflectionParameter $parameter): object|array
    {
        $className = Utilities::getParameterClassName($parameter);

        $id = $this->getAlias($className);

        if (!is_array($resolver = $this->getContextualConcrete($id))) {
            return $this->make($className);
        }

        return array_map(function ($id) {
            return $this->resolve($id);
        }, $resolver);
    }

    /**
     * Throw an exception that the concrete is not instantiable.
     *
     * @param string $resolver
     * @return void
     *
     * @throws BindingResolutionException
     */
    protected function notInstantiable(string $resolver)
    {
        if (!empty($this->buildStack)) {
            $previous = implode(', ', $this->buildStack);

            $message = "Target [$resolver] is not instantiable while building [$previous].";
        } else {
            $message = "Target [$resolver] is not instantiable.";
        }

        throw new BindingResolutionException($message);
    }

    /**
     * Throw an exception for an unresolvable primitive.
     *
     * @param ReflectionParameter $parameter
     * @return void
     *
     * @throws BindingResolutionException
     */
    protected function unresolvablePrimitive(ReflectionParameter $parameter)
    {
        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

        throw new BindingResolutionException($message);
    }

    /**
     * Creates an instance of a bound class.
     *
     * @param string $id
     * @return mixed
     */
    public function make(string $id): mixed
    {
        return $this->resolve($id);
    }


    /**
     * Binds a concrete instance and returns it.
     *
     * @param string $id
     * @param object|string $instance
     * @return object|string
     */
    public function instance(string $id, object|string $instance): object|string
    {
        return $this->instances[$id] = $instance;
    }

    /**
     * Alias a type to a different name.
     *
     * @param string
     * @param string $alias
     * @return void
     *
     * @throws LogicException
     */
    public function alias(string $id, string $alias)
    {
        if ($id === $alias) {
            throw new LogicException("[$id] is aliased to itself.");
        }

        $this->aliases[$alias] = $id;

        $this->aliasesById[$id][] = $alias;
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param callable|array|string $callback
     * @param array<string, mixed> $parameters
     * @param string|null $defaultMethod
     * @return mixed
     *
     * @throws BindingResolutionException
     * @throws CircularDependencyException
     * @throws ReflectionException
     */
    public function call(callable|array|string $callback, array $parameters = [], string $defaultMethod = null): mixed
    {
        return BoundMethod::call($this, $callback, $parameters, $defaultMethod);
    }

    /**
     * Get the alias for an abstract if available.
     *
     * @param string $id
     * @return string
     */
    public function getAlias(string $id): string
    {
        return isset($this->aliases[$id])
            ? $this->getAlias($this->aliases[$id])
            : $id;
    }


    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance(): Container
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param Container|null $container
     * @return Container|ContainerAgreement|null
     */
    public static function setInstance(ContainerAgreement $container = null): Container|ContainerAgreement|null|static
    {
        return static::$instance = $container;
    }

    /**
     * Get the extender callbacks for a given type.
     *
     * @param string $id
     * @return array
     */
    protected function getExtenders(string $id): array
    {
        return $this->extenders[$this->getAlias($id)] ?? [];
    }
}