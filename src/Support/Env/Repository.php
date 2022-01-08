<?php

namespace Curfle\Support\Env;

use Curfle\Agreements\Support\Env\Repository as RepositoryAgreement;
use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;

class Repository implements RepositoryAgreement
{
    /**
     * The repositorys path to the .env file.
     *
     * @var string|null
     */
    private ?string $path;

    /**
     * The parsed content of the .env file.
     *
     * @var array|null
     */
    private ?array $variables = null;

    /**
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }

    /**
     * Loads the .env file.
     *
     * @return Repository
     */
    public function load() : static
    {
        if($this->path === null)
            return $this;

        if (!file_exists($this->path))
            die("The .env file [{$this->path}] could not be found.");

        // obtain all lines and parse them
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->variables = Parser::parse($lines);

        // make vars available globally
        $this->makeEnvAvailableGlobally();

        return $this;
    }

    /**
     * Fills the $_ENV and $_SERVER variables with the loaded config
     */
    private function makeEnvAvailableGlobally()
    {
        foreach ($this->variables as $variable => $value) {
            if (!array_key_exists($variable, $_SERVER) && !array_key_exists($variable, $_ENV)) {
                putenv(sprintf('%s=%s', $variable, $value));
                $_ENV[$variable] = $value;
                $_SERVER[$variable] = $value;
            }
        }
    }

    /**
     * @param string|null $path
     * @return Repository
     */
    public function setPath(?string $path): static
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): ?string
    {
        return $this->variables[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function set(string $name, string $value): bool
    {
        $this->variables[$name] = $value;
        return true;
    }
}