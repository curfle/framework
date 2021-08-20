<?php

namespace Curfle\Agreements\Auth;

use Curfle\Http\Request;

interface Guardian
{
    /**
     * Returns all supported drivers.
     *
     * @return array
     */
    public function drivers(): array;

    /**
     * Adds a supported driver.
     *
     * @param string $name
     * @return $this
     */
    public function addDriver(string $name): static;

    /**
     * Returns if a driver is supported by this guardian.
     *
     * @param string $driver
     * @return bool
     */
    public function supports(string $driver): bool;

    /**
     * Sets the authenticatable.
     *
     * @param ?string $authenticatableClass
     * @return $this
     */
    public function setAuthenticatable(?string $authenticatableClass): static;

    /**
     * Validates a request. Returns true if the request successfully passed all checks.
     * If an authenticatable was provided, it gets instanciated and made available under
     * the Auth facade. If the validation fails, false will be returned.
     *
     * @param Request $request
     * @return bool
     */
    public function validate(Request $request): bool;
}