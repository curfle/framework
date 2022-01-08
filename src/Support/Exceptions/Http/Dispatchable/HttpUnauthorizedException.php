<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Throwable;

class HttpUnauthorizedException extends HttpDispatchableException
{
    public function __construct(string $message = "Unauthorized", int $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}