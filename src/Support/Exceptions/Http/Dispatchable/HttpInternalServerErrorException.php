<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Exception;
use Throwable;

class HttpInternalServerErrorException extends HttpDispatchableException
{
    public function __construct(string $message = "Internal Server Error", int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}