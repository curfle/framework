<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Exception;
use Throwable;

class HttpBadRequestException extends HttpDispatchableException
{
    public function __construct(string $message = "Bad Request", int $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}