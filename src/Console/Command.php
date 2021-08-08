<?php

namespace Curfle\Console;

use Closure;
use Curfle\Essence\Application;

class Command
{

    /**
     * The commands' application instance.
     *
     * @var Application
     */
    private Application $app;

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
    protected Closure|null $resolver = null;

    /**
     * The conditions for the parameter.
     *
     * @var array
     */
    protected array $where = [];

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
    public function __construct(Application $app, string $signature, Closure $resolver = null)
    {
        $this->app = $app;
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
            $this->app->call($closure);
        }
        return $this->output;
    }

    /**
     * Returns the commands' application instance.
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Returns the commands'signature.
     *
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Creates a prompt and returns the users' answer.
     *
     * @param string $message
     * @param bool $addNewline
     * @return string
     */
    protected function prompt(string $message, bool $addNewline = true): string
    {
        return readline($message);
    }

    /**
     * Writes a string to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @param int $color
     * @return Command
     */
    protected function write(?string $message, bool $addNewline = true, int $color = 0): static
    {
        $this->output->write($message, $addNewline, $color);
        return $this;
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return Command
     */
    protected function warning(?string $message, bool $addNewline = true): static
    {
        $this->output->warning($message, $addNewline);
        return $this;
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return Command
     */
    protected function error(?string $message, bool $addNewline = true): static
    {
        $this->output->error($message, $addNewline);
        return $this;
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return Command
     */
    protected function success(?string $message, bool $addNewline = true): static
    {
        $this->output->success($message, $addNewline);
        return $this;
    }

    /**
     * Flushes the output.
     *
     * @return Command
     */
    protected function flush(): static
    {
        $this->output->flush();
        return $this;
    }

    /**
     * Clears the output.
     *
     * @return Command
     */
    protected function clear(): static
    {
        $this->output->clear();
        return $this;
    }
}