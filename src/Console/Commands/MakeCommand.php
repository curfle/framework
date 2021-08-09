<?php


namespace Curfle\Console\Commands;

use Curfle\Console\Command;
use Curfle\Essence\Application;
use Curfle\Filesystem\Filesystem;
use Curfle\Support\Exceptions\FileSystem\FileAlreadyExists;
use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;
use Curfle\Support\Exceptions\Misc\InvalidArgumentException;
use Curfle\Support\Str;
use Curfle\Console\Input;

abstract class MakeCommand extends Command
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected FileSystem $files;

    /**
     * Reserved names that cannot be used for generation.
     *
     * @var string[]
     */
    protected array $reservedNames = [
        "__halt_compiler",
        "abstract",
        "and",
        "array",
        "as",
        "break",
        "callable",
        "case",
        "catch",
        "class",
        "clone",
        "const",
        "continue",
        "declare",
        "default",
        "die",
        "do",
        "echo",
        "else",
        "elseif",
        "empty",
        "enddeclare",
        "endfor",
        "endforeach",
        "endif",
        "endswitch",
        "endwhile",
        "eval",
        "exit",
        "extends",
        "final",
        "finally",
        "fn",
        "for",
        "foreach",
        "function",
        "global",
        "goto",
        "if",
        "implements",
        "include",
        "include_once",
        "instanceof",
        "insteadof",
        "interface",
        "isset",
        "list",
        "namespace",
        "new",
        "or",
        "print",
        "private",
        "protected",
        "public",
        "require",
        "require_once",
        "return",
        "static",
        "switch",
        "throw",
        "trait",
        "try",
        "unset",
        "use",
        "var",
        "while",
        "xor",
        "yield",
    ];

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->files = $app["files"];
    }

    /**
     * Get the template file for the generator.
     *
     * @return string
     */
    abstract protected function getTemplate(): string;

    /**
     * Takes a full qualified class name and returns a file name.
     *
     * @param string $name
     * @param bool $addUniqueTimestamp
     * @return string
     */
    protected function createFileName(string $name, bool $addUniqueTimestamp = false): string
    {
        $nameParts = explode("\\", $name);
        return ($addUniqueTimestamp ? date('YmdHis') . "_" : "")
            . end($nameParts)
            . ".php";
    }

    /**
     * Make the class file.
     *
     * @param string $name
     * @param string $filename
     * @throws InvalidArgumentException
     * @throws FileAlreadyExists|FileNotFoundException
     */
    protected function makeFile(string $name, string $filename)
    {
        // check if name is reserved
        if ($this->isReservedName($name))
            throw new InvalidArgumentException("The name [$name] is reserved by PHP.");

        // check if file already exists
        if ($this->alreadyExists($filename))
            throw new FileAlreadyExists("$name already exists");

        $this->makeDirectory($filename);

        $this->files->put($filename, $this->buildClass($name));

        $this->success($name . " created successfully.");
    }

    /**
     * Determine if the class already exists.
     *
     * @param string $file
     * @return bool
     */
    protected function alreadyExists(string $file): bool
    {
        return $this->files->exists($file);
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath(string $name): string
    {
        return $this->files->dirname($name);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory(string $path): string
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     *
     * @throws FileNotFoundException
     */
    protected function buildClass(string $name): string
    {
        $stub = $this->files->get($this->getTemplate());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the namespace for the given template.
     *
     * @param string $template
     * @param string $name
     * @return $this
     */
    protected function replaceNamespace(string &$template, string $name): static
    {
        $searches = [
            ["DummyNamespace"],
        ];

        foreach ($searches as $search) {
            $template = str_replace(
                $search,
                [$this->getNamespace($name)],
                $template
            );
        }

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace(string $name): string
    {
        return trim(
            implode(
                "\\",
                array_slice(
                    explode(
                        "\\",
                        $name
                    ),
                    0,
                    -1
                )
            ),
            "\\"
        );
    }

    /**
     * Replace the class name for the given template.
     *
     * @param string $template
     * @param string $name
     * @return string
     */
    protected function replaceClass(string $template, string $name): string
    {
        $class = str_replace($this->getNamespace($name) . "\\", "", $name);
        return str_replace(["DummyClass", "{{ class }}", "{{class}}"], $class, $template);
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace(): string
    {
        var_dump($this->app->namespace());
        return $this->app->namespace();
    }

    /**
     * Checks whether the given name is reserved.
     *
     * @param string $name
     * @return string
     */
    protected function isReservedName(string $name): string
    {
        $name = strtolower($name);

        return in_array($name, $this->reservedNames);
    }
}