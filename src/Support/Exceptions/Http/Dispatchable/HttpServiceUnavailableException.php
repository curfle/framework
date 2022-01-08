<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Throwable;

class HttpServiceUnavailableException extends HttpDispatchableException
{
    public function __construct(string $message = "Service Unavailable", int $code = 503, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}