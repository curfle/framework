<?php

namespace Curfle\Http;

abstract class Middleware
{
    /**
     * Parameters that can be passed via ->middleware("middleware:param1:param2");
     *
     * @var array
     */
    protected array $parameters = [];

    /**
     * Sets the parameters passed via middleware alias.
     *
     * @param array $parameters
     * @return Middleware
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Returns a passed parameter via the middleware alias.
     *
     * @param int $index
     * @return string|null
     */
    protected function parameter(int $index): ?string
    {
        return $this->parameters[$index] ?? null;
    }


    /**
     * Handles an incoming request.
     *
     * Hint: other required dependencies can be obtained via dependency injection.
     *
     * @param Request $request
     */
    abstract function handle(Request $request);
}