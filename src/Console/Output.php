<?php

namespace Curfle\Console;

class Output
{
    public const CONSOLE_COLOR_BLACK = 1;
    public const CONSOLE_COLOR_WHITE = 2;
    public const CONSOLE_COLOR_GREEN = 4;
    public const CONSOLE_COLOR_ORANGE = 8;
    public const CONSOLE_COLOR_RED = 16;
    public const CONSOLE_COLOR_DEFAULT = 32;

    /**
     * @var string|array
     */
    private string|array $content = "";

    /**
     * @param string $content
     */
    public function __construct(string $content = "")
    {
        $this->content($content);
    }

    /**
     * Sets the response content.
     *
     * @param string|array $content
     * @return $this
     */
    public function content(string|array $content): static
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Writes a string to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @param int $color
     * @return void;
     */
    public function write(?string $message, bool $addNewline = true, int $color = 0)
    {
        $color = match ($color) {
            static::CONSOLE_COLOR_BLACK => "\033[30m",
            static::CONSOLE_COLOR_WHITE => "\033[97m",
            static::CONSOLE_COLOR_GREEN => "\033[32m",
            static::CONSOLE_COLOR_ORANGE => "\033[33m",
            static::CONSOLE_COLOR_RED => "\033[31m",
            default => "\033[39m",
        };
        $this->content .= $color . ($message ?? "") . "\033[39m" . ($addNewline ? "\n" : "");
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return void;
     */
    public function warning(?string $message, bool $addNewline = true)
    {
        $this->write($message, $addNewline, static::CONSOLE_COLOR_ORANGE);
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return void;
     */
    public function error(?string $message, bool $addNewline = true)
    {
        $this->write($message, $addNewline, static::CONSOLE_COLOR_RED);
    }

    /**
     * Writes a warning to the commands output.
     *
     * @param ?string $message
     * @param bool $addNewline
     * @return void;
     */
    public function success(?string $message, bool $addNewline = true)
    {
        $this->write($message, $addNewline, static::CONSOLE_COLOR_GREEN);
    }

    /**
     * Sends response and clears buffer.
     *
     * @return $this
     */
    public function flush(): static
    {
        $this->send();
        $this->clear();
        return $this;
    }

    /**
     * Clears the buffer.
     *
     * @return $this
     */
    public function clear(): static
    {
        $this->content = "";
        return $this;
    }

    /**
     * Sends the response.
     *
     * @return $this
     */
    public function send(): static
    {
        $this->sendContent();
        return $this;
    }

    /**
     * Sends the content for the current response.
     *
     * @return $this
     */
    public function sendContent(): static
    {
        echo $this->content;

        return $this;
    }

    /**
     * Returns the content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return trim($this->content, "\n");
    }
}