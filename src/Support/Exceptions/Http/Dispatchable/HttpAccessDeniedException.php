<?php

namespace Curfle\Support\Exceptions\Http\Dispatchable;

use Curfle\Support\Exceptions\Http\HttpDispatchableException;
use Throwable;

class HttpAccessDeniedException extends HttpDispatchableException
{
    public function __construct(string $message = "Access Denied", int $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}