<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Throwable;

class HttpMethodNotAllowedException extends HttpDispatchableException
{
    public function __construct(string $message = "Method Not Allowed", int $code = 405, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}