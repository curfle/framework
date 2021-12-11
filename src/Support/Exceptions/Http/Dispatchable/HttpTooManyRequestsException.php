<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Exception;
use Throwable;

class HttpTooManyRequestsException extends HttpDispatchableException
{
    public function __construct(string $message = "Too Many Requests", int $code = 429, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}