<?php

namespace Curfle\Agreements\Auth;

use Curfle\Auth\Authenticatable;
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
     * Attempts a login within the authenticatable class via the ::attempt()
     * method and returns true on success and false on failure. This method
     * does only return wether the credentials are valid or not, but does not
     * store the authenticated user within the guardian (see function login()).
     *
     * @param array $credentials
     * @return bool
     */
    public function attempt(array $credentials): bool;

    /**
     * Logs the authenticatable in by its identifier and sets the guardians'
     * authenticated user, which can be accessed via the Auth::user() method.
     *
     * @param mixed $id
     */
    public function login(mixed $id);

    /**
     * Returns wether the guardian has authenticated a user or not.
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Returns the current authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable;

    /**
     * Validates a request. Returns true if the request successfully passed all checks.
     * If an authenticatable was provided, it gets instanciated and set as the guardians
     * authenticated user object, wich can be access via the Auth::user() method. If the
     * validation fails, false will be returned.
     *
     * @param Request $request
     * @return bool
     */
    public function validate(Request $request): bool;
}