<?php

namespace Curfle\Auth\Guardians;

use Curfle\Agreements\Auth\Guardian as GuardianAgreement;
use Curfle\Auth\JWT\JWT;
use Curfle\Http\Request;
use Curfle\Support\Exceptions\Auth\DriverNotSupportedException;
use Curfle\Support\Exceptions\Auth\MissingAuthenticatableException;
use Curfle\Support\Exceptions\Misc\SecretNotPresentException;
use Curfle\Support\Facades\Auth;

abstract class Guardian implements GuardianAgreement
{
    const DRIVER_BEARER = "BEARER";
    const DRIVER_SESSION = "SESSION";

    /**
     * Used drivers.
     *
     * @var array
     */
    protected array $drivers = [];

    /**
     * Supported drivers.
     *
     * @var array
     */
    protected array $supported = [];

    /**
     * The class which gets instanciated after authentication.
     *
     * @var ?string
     */
    protected ?string $authenticatableClass = null;

    /**
     * @inheritDoc
     */
    public function drivers(): array
    {
        return $this->drivers;
    }

    /**
     * @inheritDoc
     * @throws DriverNotSupportedException
     */
    public function addDriver(string $name): static
    {
        if (!in_array($name, $this->supported))
            throw new DriverNotSupportedException("The driver [$name] is not supported by this guard.");
        $this->drivers[] = $name;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $driver): bool
    {
        return in_array($driver, $this->drivers());
    }

    /**
     * @inheritDoc
     */
    public function setAuthenticatable(?string $authenticatableClass): static
    {
        $this->authenticatableClass = $authenticatableClass;
        return $this;
    }

    /**
     * Returns the authenticatable class name.
     *
     * @return string|null
     */
    public function authenticatableClass(): ?string
    {
        return $this->authenticatableClass;
    }

    /**
     * Returns wether an authenticatable is available.
     *
     * @return bool
     */
    public function hasAuthenticatable(): bool
    {
        return $this->authenticatableClass !== null;
    }

    /**
     * @inheritDoc
     * @throws MissingAuthenticatableException
     */
    public function attempt(array $credentials): bool
    {
        if (!$this->hasAuthenticatable())
            throw new MissingAuthenticatableException("No authenticatable class was provided.");

        return call_user_func("{$this->authenticatableClass()}::attempt", $credentials);
    }

    /**
     * Logs the authenticatable in.
     *
     * @param mixed $id
     */
    protected function login(mixed $id)
    {
        if ($this->hasAuthenticatable()) {
            Auth::login(
                call_user_func(
                    "{$this->authenticatableClass()}::fromIdentifier",
                    $id
                )
            );
        }
    }

    /**
     * @inheritDoc
     */
    abstract public function validate(Request $request): bool;
}