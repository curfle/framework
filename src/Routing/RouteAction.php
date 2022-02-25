<?php

namespace Curfle\Routing;

use Closure;
use Curfle\Support\Arr;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Support\Exceptions\Routing\MissingControllerInformationException;

class RouteAction
{
    /**
     * Parses a route action into an array for executing in the route.
     *
     * @param string $uri
     * @param callable|array|null $action
     * @return array
     * @throws MissingControllerInformationException
     */
    public static function parse(string $uri, callable|array|null $action): array
    {
        // add default action when no action provided
        if ($action === null)
            return [
                "useController" => false,
                "callable" => static::missingAction($uri)
            ];

        // set callable action if a callable is provided
        if (is_callable($action))
            return [
                "useController" => false,
                "callable" => $action
            ];

        // check for enough information for controller method
        if (!Arr::is($action) || count($action) != 2)
            throw new MissingControllerInformationException("The [action] parameter of the route [$uri] should consist of an array of form [Controller:class, \"method\"]");

        // set controller action
        return [
            "useController" => true,
            "controller" => $action[0],
            "method" => $action[1]
        ];
    }

    /**
     * Get an action for a route that has no action.
     *
     * @param string $uri
     * @return Closure
     */
    protected static function missingAction(string $uri): Closure
    {
        return function () use ($uri) {
            throw new LogicException("Route for [{$uri}] has no action.");
        };
    }
}