<?php

namespace Curfle\Console;

use Closure;
use Curfle\Essence\Application;
use Curfle\Support\Arr;
use Curfle\Support\Str;

class Command
{

    private const DEFAULT_PARAMETER_REGEX = "\w+";

    /**
     * The commands' application instance.
     *
     * @var Application
     */
    protected Application $app;

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
     * The conditions for the arguments.
     *
     * @var array
     */
    protected array $where = [];

    /**
     * The matched parameters from the last matches() call.
     *
     * @var array|null
     */
    protected ?array $matchedParameters = null;

    /**
     * The commands' output.
     *
     * @var Output
     */
    protected Output $output;

    /**
     * @param Buddy $app
     */
    public function __construct(Application $app)
    {
        $this->setApplication($app)
            ->newOutput()
            ->install();
    }

    /**
     * Is called after construction and to be used by specific commands to set up logic (e.g. where conditions).
     *
     * @return void
     */
    protected function install()
    {
    }

    /**
     * Creates a new output for the command.
     *
     * @return $this
     */
    public function newOutput(): static
    {
        $this->output = new Output();
        return $this;
    }

    /**
     * Sets the signature.
     *
     * @param string $signature
     * @return Command
     */
    public function signature(string $signature): static
    {
        $this->signature = $signature;
        return $this;
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
     * Sets the resolver.
     *
     * @param callable|null $resolver
     * @return Command
     */
    public function resolver(?callable $resolver): static
    {
        $this->resolver = $resolver;
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
    protected function getMatches(string $string): ?array
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
        $parameterRegex = '/{(-|[a-z]|[A-Z]|[0-9])*\??}/m';
        preg_match_all($parameterRegex, $this->signature, $whereMatches, PREG_OFFSET_CAPTURE);

        $whereMatches = $whereMatches[0];
        foreach ($whereMatches as $i => $match) {
            // get the name of the parameter
            $name = rtrim(
                Str::trim($match[0], "{}"),
                "?"
            );

            // get index in matches
            $index = 1 + $i;
            for ($j = 0; $j < $i; $j++) {
                $matchName = rtrim(
                    Str::trim($whereMatches[$j][0], "{}"),
                    "?"
                );
                $index += preg_match("/\([^\?][^=]/", $this->where[$matchName] ?? self::DEFAULT_PARAMETER_REGEX);
            }

            // set parameter value
            $value = Str::trim($matches[$index][0][0] ?? "");
            if ($value !== "")
                $parameters[$name] = $value;
        }

        // cache params
        $this->matchedParameters = $parameters;

        return $parameters;
    }

    /**
     * Compiles the commands' signature and fills in missing $this->where entries.
     *
     * @return string
     */
    protected function compileSignature(): string
    {
        $signature = $this->signature;

        // search for optional parameters
        $signature = preg_replace_callback('~ {([^}]*)\?}~', function ($m) {
            return "( " . ($this->where[$m[1]] ?? self::DEFAULT_PARAMETER_REGEX) . ")?";
        }, $signature);

        // search for necessary parameters
        $signature = preg_replace_callback('~{([^}]*)}~', function ($m) {
            return "(" . ($this->where[$m[1]] ?? self::DEFAULT_PARAMETER_REGEX) . ")?";
        }, $signature);

        // replace backslashes
        $signature = Str::replace($signature, "\/", "/");
        $signature = Str::replace($signature, "/", "\/");

        return "/^$signature$/m";
    }

    /**
     * Calls the commands' resolver.
     *
     * @return Output
     */
    public function run(): Output
    {

        if ($this->resolver !== null) {
            $closure = Closure::bind($this->resolver, $this, static::class);
            $this->app->call($closure);
        } else if (method_exists($this, "handle")) {
            $this->app->call([$this, "handle"]);
        }


        return $this->output;
    }

    /**
     * Returns the commands' application instance.
     *
     * @return Buddy
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Sets the commands' application instance.
     *
     * @param Buddy $app
     * @return Command
     */
    public function setApplication(Application $app): static
    {
        $this->app = $app;
        return $this;
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
     * Returns the commands'signature.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Creates a prompt and returns the users' answer.
     *
     * @param string $message
     * @return string
     */
    protected function prompt(string $message): string
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