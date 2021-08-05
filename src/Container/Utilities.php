<?php

namespace Curfle\Container;

use Closure;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @internal
 */
class Utilities
{
    /**
     * Return the default value of the given value.
     *
     * From global value() helper in Illuminate\Support.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function unwrapIfClosure(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Get the class name of the given parameter's type, if possible.
     *
     * From Reflector::getParameterClassName() in Illuminate\Support.
     *
     * @param ReflectionParameter $parameter
     * @return string|null
     */
    public static function getParameterClassName(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }
}