<?php

namespace Curfle\Http;

class Request
{

    /**
     * @var string|null
     */
    private ?string $method = null;

    /**
     * @var string|null
     */
    private ?string $uri = null;

    /**
     * @var string|null
     */
    private ?string $host = null;

    /**
     * @var bool|null
     */
    private ?bool $https = null;

    /**
     * @var array|null
     */
    private ?array $headers = null;

    /**
     * @var string|null
     */
    private ?string $ip = null;

    /**
     * All Inputs that were sent with the request.
     *
     * @var array
     */
    private array $inputs = [];

    public function __construct(string $method = null, string $uri = null, string $host = null, bool $https = null, array $headers = null, array $inputs = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->host = $host;
        $this->https = $https;
        $this->headers = $headers;
        $this->inputs = $inputs;
    }

    /**
     * Returns the used HTTP method.
     *
     * @return string
     */
    public static function getMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    /**
     * Returns the HTTP host
     *
     * @return string
     */
    public static function getHost(): string
    {
        return $_SERVER["HTTP_HOST"];
    }

    /**
     * Returns the HTTP uri
     *
     * @return string
     */
    public static function getUri(): string
    {
        return urldecode($_SERVER["REQUEST_URI"]);
    }

    /**
     * Returns if HTTPS is used
     *
     * @return string
     */
    public static function isHTTPS(): string
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Returns if HTTPS is used
     *
     * @return string
     */
    public static function getHeaders(): string
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Returns all inputs sent with the HTTP request.
     *
     * @return array
     */
    public static function getInputs(): array
    {
        $inputs = [];
        parse_str(file_get_contents('php://input'), $inputs);
        return $inputs;
    }

    /**
     * Captures the current request.
     *
     * @return Request
     */
    public static function capture(): static
    {
        return new Request(
            static::getMethod(),
            static::getUri(),
            static::getHost(),
            static::isHTTPS(),
            getallheaders(),
            self::getInputs()
        );
    }

    /**
     * @return string|null
     */
    public function method(): ?string
    {
        return $this->method;
    }

    /**
     * @param string|null $method
     * @return Request
     */
    public function setMethod(?string $method): static
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string|null
     */
    public function uri(): ?string
    {
        return $this->uri;
    }

    /**
     * @param string|null $uri
     * @return Request
     */
    public function setUri(?string $uri): static
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return string|null
     */
    public function host(): ?string
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     * @return Request
     */
    public function setHost(?string $host): static
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function https(): ?bool
    {
        return $this->https;
    }

    /**
     * @param bool|null $https
     * @return Request
     */
    public function setHttps(?bool $https): static
    {
        $this->https = $https;
        return $this;
    }

    /**
     * @return array|null
     */
    public function headers(): ?array
    {
        return $this->headers;
    }

    /**
     * @param array|null $headers
     * @return Request
     */
    public function setHeaders(?array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return string|null
     */
    public function ip(): ?string
    {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     * @return Request
     */
    public function setIp(?string $ip): static
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Returns if an input value exists.
     *
     * @param string $name
     * @return bool
     */
    public function hasInput(string $name): bool
    {
        return array_key_exists($name, $this->inputs);
    }

    /**
     * Returns all input values.
     *
     * @return mixed
     */
    public function inputs(): array
    {
        return $this->inputs;
    }

    /**
     * Returns an input value.
     *
     * @param string $name
     * @return mixed
     */
    public function input(string $name): mixed
    {
        return $this->inputs[$name];
    }

    /**
     * Returns if a header value exists.
     *
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * Returns a header value.
     *
     * @param string $name
     * @return string|null
     */
    public function header(string $name): ?string
    {
        return $this->headers[$name];
    }

    /**
     * Sets an input manually - e.g. used by parameter inputs.
     *
     * @param string $name
     * @param mixed $value
     * @return Request
     */
    public function addInput(string $name, mixed $value): static
    {
        $this->inputs[$name] = $value;
        return $this;
    }
}