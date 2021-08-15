<?php

namespace Curfle\Utilities;

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
     * @param string $filePathName
     * @return ?string
     */
    public static function getClassNameFromFile(string $filePathName): ?string
    {
        $php_code = file_get_contents($filePathName);

        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        return $classes[0] ?? null;
    }


}