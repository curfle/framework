<?php

namespace Curfle\Routing;

use Curfle\Support\Exceptions\Routing\NonExistingControllerMethodException;

abstract class Controller{

    /**
     * Execute an action on the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function callAction(string $method, array $parameters): mixed
    {
        return $this->{$method}(...$parameters);
    }

    /**
     * Handle missing actions on controller.
     *
     * @param string $name
     * @param array $arguments
     * @throws NonExistingControllerMethodException
     */
    public function __call(string $name, array $arguments)
    {
        throw new NonExistingControllerMethodException(sprintf(
            "Method %s::%s does not exist.", static::class, $name
        ));
    }
}