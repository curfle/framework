<?php

namespace Curfle\Support\Exceptions\Http;

use Curfle\Support\Exceptions\CurfleException;
use Throwable;

class HttpDispatchableException extends HttpException
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Sets the code sent as header.
     *
     * @param int $code
     * @return static
     */
    public function code(int $code): static
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Sets the message sent as content.
     *
     * @param string $message
     * @return static
     */
    public function message(string $message): static
    {
        $this->message = $message;
        return $this;
    }
}