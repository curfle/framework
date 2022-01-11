<?php

namespace Curfle\Utilities;

use Curfle\Support\Facades\File;
use Curfle\Support\Str;
use ReflectionNamedType;
use ReflectionParameter;

class Utilities
{
    /**
     * Get the class name of the given parameter's type, if possible.
     *
     * @param ReflectionParameter $parameter
     * @return string|null
     */
    public static function getParameterClassName(ReflectionParameter $parameter): string|null
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin())
            return null;

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === "self")
                return $class->getName();

            if ($name === "parent" && $parent = $class->getParentClass())
                return $parent->getName();
        }

        return $name;
    }

    /**
     * Returns the classname of a class contained in a file.
     *
     * @param string $file
     * @param bool $includeNamespace
     * @return ?string
     */
    public static function getClassNameFromFile(string $file, bool $includeNamespace = true): ?string
    {
        $code = file_get_contents($file);
        $class = $namespace = "";

        $tokens = token_get_all($code);

        for ($i = 0; $i < count($tokens); $i++) {
            // find namespace
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < count($tokens) && Str::empty($namespace); $j++) {
                    if ($tokens[$j][0] === T_NAME_QUALIFIED)
                        $namespace = $tokens[$j][1] . "\\";
                }
            }

            // find classname
            if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j] === '{')
                        $class = $tokens[$i + 2][1];
                }
            }
        }

        return $includeNamespace ? $namespace . $class : $class;
    }
}