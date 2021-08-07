<?php

namespace Curfle\Console;

use Closure;

class Command
{

    /**
     * The commands' signature.
     *
     * @var string
     */
    protected string $signature;

    /**
     * The commands' description.
     *
     * @var string
     */
    protected string $description = "";

    /**
     * The commands' resolver closure.
     *
     * @var Closure|null
     */
    private Closure|null $resolver = null;

    /**
     * The conditions for the parameter.
     *
     * @var array
     */
    private array $where = [];

    /**
     * The matched parameters from the last matches() call.
     *
     * @var array|null
     */
    private ?array $matchedParameters = null;

    /**
     * The commands' output.
     *
     * @var Output
     */
    private Output $output;

    /**
     * @param Closure|null $resolver
     */
    public function __construct(string $signature, Closure $resolver = null)
    {
        $this->signature = $signature;
        $this->resolver = $resolver;
        $this->output = new Output();
    }

    /**
     * Sets the commands' descritption.
     *
     * @param string $description
     * @return $this
     */
    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Adds a regex condition to a parameter.
     *
     * @param string $parameter
     * @param string $regex
     * @return $this
     */
    public function where(string $parameter, string $regex): static
    {
        $this->where[$parameter] = $regex;
        return $this;
    }

    /**
     * Returns whether a command matches the
     * @param Input $input
     * @return bool
     */
    public function matches(Input $input): bool
    {
        return $this->getMatches($input->input()) !== null;
    }

    /**
     * Returns the matched parameters from the last ->matches(...) call.
     *
     * @return array|null
     */
    public function getMatchedParameters(): ?array
    {
        return $this->matchedParameters;
    }

    /**
     * Returns all matches of the string and its parameters against this command.
     *
     * @param string $string
     * @return array|null
     */
    private function getMatches(string $string): ?array
    {
        $parameters = [];

        // find parameter matches
        $matches = [];
        $signature = $this->compileSignature();
        preg_match_all($signature, $string, $matches, PREG_OFFSET_CAPTURE);

        // if string does not match -> return null
        if (empty($matches[0]))
            return null;

        // sort where conditions in order of string
        $whereMatches = [];
        $parameterRegex = '/{([a-z]|[A-Z]|[0-9])*\??}/m';
        preg_match_all($parameterRegex, $this->signature, $whereMatches, PREG_OFFSET_CAPTURE);
        foreach ($whereMatches[0] as $i => $match) {
            $name = str_replace(
                "?",
                "",
                substr($match[0], 1, -1)
            );
            $parameters[$name] = trim($matches[$i + 1][0][0]);
        }

        // cache params
        $this->matchedParameters = $parameters;

        return $parameters;
    }

    /**
     * Compiles the comamnds' signature.
     *
     * @return string
     */
    private function compileSignature(): string
    {
        $signature = $this->signature;
        foreach ($this->where as $parameter => $regex) {
            $signature = str_replace("{{$parameter}}", "($regex)", $signature);
            $signature = str_replace(" {{$parameter}?}", "( $regex)?", $signature);
        }

        $signature = str_replace("/", "\/", $signature);

        return "/^$signature$/m";
    }

    /**
     * Calls the commands' resolver.
     *
     * @param Input $input
     * @return Output
     */
    public function run(Input $input): Output
    {

        if ($this->resolver !== null) {
            $closure = Closure::bind($this->resolver, $this, static::class);
            $closure($input);
        }
        return $this->output;
    }

    /**
     * Writes a string to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @param int $color
     * @return void;
     */
    private function write(?string $message, bool $addNewline = true, int $color = 0)
    {
        $this->output->write($message, $addNewline, $color);
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return void;
     */
    private function warning(?string $message, bool $addNewline = true)
    {
        $this->output->warning($message, $addNewline);
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return void;
     */
    private function error(?string $message, bool $addNewline = true)
    {
        $this->output->error($message, $addNewline);
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return void;
     */
    private function success(?string $message, bool $addNewline = true)
    {
        $this->output->success($message, $addNewline);
    }
}