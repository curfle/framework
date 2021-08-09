<?php

namespace Curfle\Console;

class Input
{

    /**
     * @var array
     */
    private array $argv = [];

    /**
     * @var array
     */
    private array $namedArguments = [];

    /**
     * All Inputs that were sent with the request.
     *
     * @var array
     */
    private array $inputs = [];

    public function __construct(array $argv = [])
    {
        $this->argv = $argv;
    }

    /**
     * Returns all arguments passed to the script via the console.
     *
     * @return array
     */
    private static function getArgv(): array
    {
        return array_slice($_SERVER["argv"], 1);
    }

    /**
     * Captures the current request.
     *
     * @return Input
     */
    public static function capture(): static
    {
        return new Input(
            static::getArgv()
        );
    }

    /**
     * Creates a request from a string (e.g. "php buddy inspire")
     *
     * @param string $string
     * @return Input
     */
    public static function fromString(string $string): static
    {
        return new Input(
            static::parseInput($string)
        );
    }

    /**
     * Parses a string into an argv-style array.
     *
     * @param string $input
     * @return array
     */
    private static function parseInput(string $input): array
    {
        $argv = explode(" ", $input);
        if (($argv[0] ?? null) === "php")
            unset($argv[0]);
        if (($argv[1] ?? null) === "buddy")
            unset($argv[1]);
        return array_values($argv);
    }

    /**
     * Returns the raw input string.
     *
     * @return string
     */
    public function input(): string
    {
        return implode(" ", $this->argv());
    }

    /**
     * Returns all arguments.
     *
     * @return array
     */
    public function argv(): array
    {
        return $this->argv;
    }

    /**
     * Returns the number of arguments.
     *
     * @return int
     */
    public function argc(): int
    {
        return count($this->argv);
    }

    /**
     * Returns the command name (the first argument).
     *
     * @return string|null
     */
    public function command(): ?string
    {
        return $this->argv()[0] ?? null;
    }

    /**
     * Adds a named argument to the input.
     *
     * @param string $name
     * @param mixed $value
     * @return void;
     */
    public function addNamedArgument(string $name, string $value)
    {
        $this->namedArguments[$name] = $value;
    }

    /**
     * Returns a named argument.
     *
     * @param string $name
     * @return ?string
     */
    public function namedArgument(string $name): ?string
    {
        return $this->namedArguments[$name] ?? null;
    }
}